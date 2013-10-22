<?php
/**
  * User model
  *
  * This is the model for group data.
  * @author Jaisen Mathai <jaisen@jmathai.com>
  */
class Group extends BaseModel
{
  /*
   * Constructor
   */
  public function __construct($params = null)
  {
    parent::__construct();
    if(isset($params['user']))
      $this->user = $params['user'];
    else
      $this->user = new User;
  }

  public function create($params)
  {
    $whitelist = $validParams = $this->getDefaultAttributes();
    foreach($params as $key => $value)
    {
      if(isset($whitelist[$key]))
        $validParams[$key] = $params[$key];
    }

    if(!$this->validate($validParams))
      return false;

    $nextGroupId = $this->user->getNextId('group');
    if($nextGroupId === false)
      return false;

    $res = $this->db->putGroup($nextGroupId, $validParams);
    if($res === false)
      return false;

    return $nextGroupId;
  }

  public function delete($id)
  {
    return $this->db->deleteGroup($id);
  }

  public function getGroup($id)
  {
    // TODO !group check permission
    $group = $this->db->getGroup($id);
    return $group;
  }

  public function getGroups($email = null)
  {
    // TODO !group check permission
    return $this->db->getGroups($email);
  }

  public function getGroupsByMember($email)
  {
    return $this->db->getGroupMemberGroups($email);
  }

  public function manageMembers($id, $emails, $action)
  {
    foreach($emails as $k => $v) 
    {
      if(stristr($v, '@') === false)
        unset($emails[$k]);
    }

    if(count($emails) === 0)
      return false;

    $res = false;
    switch($action)
    {
      case 'add':
        $res = $this->db->putGroupMembers($id, $emails);
        if($res)
          $this->notifyMembers($id, $emails);
        break;
      case 'remove':
        $res = $this->db->deleteGroupMembers($id, $emails);
        break;
    }
    return $res;
  }

  public function getMembersAcrossGroups()
  {
    return $this->db->getGroupsMembers();
  }

  public function getNewMembersFromList($emails)
  {
    $members = $this->getMembersAcrossGroups();
    // TODO check php function to extract colum from multi dimensional array
    $existingMembers = array();
    foreach($members as $m)
      $existingMembers[] = $m['email'];

    return array_diff($emails, $existingMembers);
  }

  public function undelete($id)
  {
    return $this->db->undeleteGroup($id);
  }

  public function update($id, $params)
  {
    $defaults = $this->getDefaultAttributes();
    $validParams = array();
    foreach($defaults as $key => $value)
    {
      if(isset($params[$key]))
        $validParams[$key] = $params[$key];
    }
    if(!$this->validate($validParams, false))
      return false;

    return $this->db->postGroup($id, $validParams);
  }

  private function getDefaultAttributes()
  {
    return array(
      'name' => '',
      'description' => '',
      'album' => null,
      'user' => null,
      'group' => null
    );
  }

  private function notifyMembers($id, $emails)
  {
    $account = new Account;
    $user = new User;
    $utility = new Utility;
    $host = $utility->getHost();

    $template = sprintf('%s/email/group-access-granted.php', $this->config->paths->templates);
    $body = getTemplate()->get($template, array(
      'siteSignInUrl' => sprintf('%s://%s/user/login', $utility->getProtocol(false), $utility->getHost()),
      'siteHost' => $host,
      'forgotPasswordUrl' => 'https://trovebox.com/password/forgot' // fix this to not be hard coded
    ));

    foreach($emails as $email)
    {
      if(!$account->emailExists($email))
        $account->create($email, true);

      // we need to instantiate a new emailer since we are sending one email per member
      $emailer = new Emailer;
      $emailer->setSubject(sprintf("You've been granted access to a Trovebox site by %s", $user->getEmailAddress()));
      $emailer->setRecipients(array($email));
      $emailer->setBody($body);
      $emailer->send();
    }
  }

  private function validate($params, $create = true)
  {
    // when creating an account we require the name
    // when updateing we check to make sure if a name is passed in that it's not empty
    if(empty($params))
      return false;
    elseif($create && (!isset($params['name']) || empty($params['name'])))
      return false;
    elseif(isset($params['name']) && empty($params['name']))
      return false;

    return true;
  }
}
