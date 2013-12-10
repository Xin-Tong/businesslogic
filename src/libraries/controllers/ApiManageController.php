<?php
class ApiManageController extends ApiBaseController
{
  public function __construct()
  {
    parent::__construct();
    getAuthentication()->requireAuthentication();
  }

  public function settingsPost()
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $host = $this->utility->getHost();
    $configFile = $this->utility->getConfigFile();
    $configString = getConfig()->getString($configFile);
    $configArray = parse_ini_string($configString, true);
    $originalAdmins = getConfig()->get('user')->admins;

    // set defaults since checkbox values are not passed if unchecked
    // skip defaults is for the admins form which doesn't pass any of these
    if(!isset($_POST['skipDefaults']))
      $post = array_merge(array('enableBetaFeatures' => 0, 'allowDuplicate' => 0, 'downloadOriginal' => 0, 'hideFromSearchEngines' => 0, 'decreaseLocationPrecision' => 0), $_POST);
    else
      $post = $_POST;

    foreach($post as $key => $value)
    {
      switch($key)
      {
        case 'admins':
          $configArray['user']['admins'] = implode(',', (array)$value);
          break;
        case 'enableBetaFeatures':
          $configArray['site']['enableBetaFeatures'] = (string)intval($value);
          break;
        case 'allowDuplicate':
          $configArray['site']['allowDuplicate'] = (string)intval($value);
          break;
        case 'downloadOriginal':
          $configArray['site']['allowOriginalDownload'] = (string)intval($value);
          break;
        case 'hideFromSearchEngines':
          $configArray['site']['hideFromSearchEngines'] = (string)intval($value);
          break;
        case 'decreaseLocationPrecision':
          $configArray['site']['decreaseLocationPrecision'] = (string)intval($value);
          break;
        case 'fileSystem':
          // validate this is an existing file system
          try
          {
            $testFs = getFs($value, false);
            $configArray['systems']['fileSystem'] = $value;
          }
          catch(Exception $e)
          {
            $this->logger->warn(sprintf('Unable to find the specified file system adapter (%s)', $value), $e);
          }
          break;
        case 'credentials':
        case 'box':
        case 'aws':
        case 'dropbox':
          if(empty($value))
            continue;

          // if credentials we need to encrypt
          if($key === 'credentials')
          {
            $tmpCredentials = json_decode($value, true);
            foreach($tmpCredentials as $k => $v)
              $tmpCredentials[$k] = $this->utility->encrypt($v);

            $value = json_encode($tmpCredentials);
          }

          // we do a merge here since it's an array of values and we don't want to clobber values not passed in
          if(isset($configArray[$key]))
            $configArray[$key] = array_merge($configArray[$key], json_decode($value, true));
          else
            $configArray[$key] = json_decode($value, true);
          break;
      }
    }
    $res = getConfig()->write($configFile, $this->utility->generateIniString($configArray, true));

    if($res)
    {
      // if admins change then we send emails and update the administrator table
      if(isset($post['admins']))
      {
        $administratorObj = new Administrator;
        $accountObj = new Account;
        $adminDiff = $this->adminDiff($post['admins'], $originalAdmins);
        // if new users we add/notifiy
        if(!empty($adminDiff['add']))
        {
          foreach($adminDiff['add'] as $em)
            $administratorObj->add($em, $host);
          $this->notifyAdministrators($adminDiff['add']);
        }
        else
        {
          foreach($adminDiff['remove'] as $em)
            $administratorObj->delete($em, $host);
        }
      }

      return $this->success('Features successfully updated', true);
    }

    return $this->error('Could not update features', false);
  }

  private function adminDiff($new, $old)
  {
    $existing = (array)explode(',', $old);
    sort($existing);

    if(!is_array($new))
      $new = (array)explode(',', $new);
    sort($new);

    return array(
      'add' => array_diff($new, $existing),
      'remove' => array_diff($existing, $new)
    );
  }

  private function notifyAdministrators($emails)
  {
    $account = new Account;
    $user = new User;
    $utility = new Utility;
    $host = $utility->getHost();

    $template = sprintf('%s/email/administrator-granted.php', $this->config->paths->templates);
    $body = getTemplate()->get($template, array(
      'siteSignInUrl' => sprintf('%s://%s/user/login', $utility->getProtocol(false), $utility->getHost()),
      'siteHost' => $host,
      'forgotPasswordUrl' => 'https://trovebox.com/password/forgot' // fix this to not be hard coded
    ));

    foreach($emails as $email)
    {
      if(empty($email))
        continue;

      if(!$account->emailExists($email))
        $account->create($email, true);

      // we need to instantiate a new emailer since we are sending one email per member
      $emailer = new Emailer;
      $emailer->setSubject(sprintf("You've been granted administrative access to a Trovebox site by %s", $user->getEmailAddress()));
      $emailer->setRecipients(array($email));
      $emailer->setBody($body);
      $emailer->send();
    }
  }
}
