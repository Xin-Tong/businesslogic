<?php
/**
 * AwsTranscoder Plugin
 *
 * @author James Walker - walkah@walkah.net
 */

// iPhone - 1351620000001-100020

use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\Sns\SnsClient;


class AwsTranscoderPlugin extends PluginBase
{

  public function __construct()
  {
    parent::__construct();

    $utilityObj = new Utility;
    $this->sns = SnsClient::factory(
      array(
        'key'    => $utilityObj->decrypt($this->config->credentials->awsKey),
        'secret' => $utilityObj->decrypt($this->config->credentials->awsSecret),
        'region' => 'us-east-1'
      )
    );
    $this->transcoder = ElasticTranscoderClient::factory(
      array(
        'key'    => $utilityObj->decrypt($this->config->credentials->awsKey),
        'secret' => $utilityObj->decrypt($this->config->credentials->awsSecret),
        'region' => 'us-east-1'
      )
    );
    
  }

  public function defineConf()
  {
    return array(
      'pipelineId' => '',
      'presets' => '',
      'bucket' => '',
    );
  }

  public function renderFooterJavascript()
  {
    return 'OP.Util.config.enabledVideo = true;';
  }
  
  public function onVideoUploaded()
  {
    parent::onVideoUploaded();

    $conf = $this->getConf();
    
    $video = $this->plugin->getData('video');
    $videoId = $this->plugin->getData('videoId');

    $args = array(
      'PipelineId' => $conf->pipelineId,
      'Input' => array(
        'Key' => substr(parse_url($video['pathOriginal'], PHP_URL_PATH), 1),
        'FrameRate' => 'auto',
        'Resolution' => 'auto',
        'AspectRatio' => 'auto',
        'Interlaced' => 'auto',
        'Container' => 'auto',
      ),
    );

    $presets = explode(',', $conf->presets);

    foreach($presets as $presetId) {
      $this->logger->warn('preset: '. $presetId);
      $paths = $this->generatePaths($presetId, $video);
      
      $args['Outputs'][] = array(
        'Key' => $paths['video'],
        'ThumbnailPattern' => $paths['thumbnail'],
        'Rotate' => 'auto',
        'PresetId' => $presetId,
      );
    }
    
    // Create ET job
    try {
      $result = $this->transcoder->createJob($args);
      $job = $result->get('Job');

      $updateParams = array();
      $updateParams['key'] = sha1($job['Id']);
      $updateParams['videoStatus'] = 'pending';
      $updateParams['videoJobId'] = $job['Id'];
      $photoObj = new Photo;
      $status = $photoObj->update($videoId, $updateParams);
    }
    catch (Exception $e) {
      $this->logger->crit("Unable to create Job: ". $e->getMessage());
    }
  }

  public function defineApis()
  {
    return array(
      'sns' => array('POST', '/sns.json', EpiApi::external)
    );
  }

  public function routeHandler($route)
  {
    switch($route) {
      case '/sns.json':
        $data = file_get_contents('php://input');
        $json = json_decode($data);

        switch ($json->Type) {
          case 'SubscriptionConfirmation':
            return $this->confirmSubscription($json);
          case 'Notification':
            return $this->handleNotification($json);
        }
        
        break;
    }
  }

  private function confirmSubscription($values)
  {
    $params = array(
      'TopicArn' => $values->TopicArn,
      'Token' => $values->Token
    );

    try {
      $this->sns->confirmSubscription($params);
    } catch (Exception $e) {
      $this->logger->crit("Unable to confirm SNS subscription: " . $e->getMessage());
    }

    return array(
      'code' => 200
    );
  }

  private function handleNotification($values)
  {
    $message = json_decode($values->Message);
    $conf = $this->getConf();

    switch ($message->state) {
      case 'ERROR':
        $this->logger->crit('Transcoding failed for jobId: '. $message->jobId);
        break;
      case 'COMPLETED':
        $photo = $this->db->getPhotoByKey(sha1($message->jobId));

        // get outputs
        list(,$output) = each($message->outputs);

        $params = array(
          'skipOriginal' => '1',
          'photo' => str_replace('{count}', '00002', sprintf('http://%s/%s.jpg', $conf->bucket, $output->thumbnailPattern)),
          'videoStatus' => 'completed',
          'videoSource' => sprintf('http://%s/%s', $conf->bucket, $output->key)
        );

        $utilityObj = new Utility;
        // create a temporary oauth credential
        // then convert it to an access token
        // then get the oauth token including all keys
        $credentialObj = getCredential();
        $consumerKey = $credentialObj->create('Transcoding Token');
        $credentialObj->convertToken($consumerKey, Credential::typeAccess);
        $oauthCredential = $credentialObj->getConsumer($consumerKey);

        try
        {
          $oauth = new OAuth($oauthCredential['id'],$oauthCredential['clientSecret'],OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_AUTHORIZATION);
          $oauth->setToken($oauthCredential['userToken'],$oauthCredential['userSecret']);
          $oauth->fetch(sprintf('http://%s/photo/%s/replace.json', $utilityObj->getHost(), $photo['id']), $params, OAUTH_HTTP_METHOD_POST);
        } catch(OAuthException $E) {
          $this->logger->crit(sprintf('OAuth error from replace call on photo (%s) : %s', $photo['id'], $E->lastResponse));
        }

        // TODO what should we do if this fails? (jmathai)
        $credentialObj->delete($consumerKey);
        break;
        
    }
    return array(
      'code' => 200
    );
  }
  
  private function generatePaths($presetId, $video)
  {
    try {
      $result = $this->transcoder->readPreset(array('Id' => $presetId));
      $preset = $result->get('Preset');
    } catch (Exception $e) {
      $this->logger->crit('Unable to read preset: '. $e->getMessage());
    }
      
    $parts = pathinfo($video['pathOriginal']);

    return array(
      'video' => sprintf('video/custom/%s/%s-%s.%s', date('Ym', $video['dateTaken']), $parts['filename'], $preset['Id'], $preset['Container']),
      'thumbnail' => sprintf('video/custom/%s/%s-{count}', date('Ym', $video['dateTaken']), $parts['filename'])
    );
  }
}
