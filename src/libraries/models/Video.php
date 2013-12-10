<?php
/**
 * Video model.
 *
 * Operations related to videos
 * @author James Walker <walkah@walkah.net>
 */
class Video extends Media
{
  public function __construct($params = null)
  {
    parent::__construct();
    
    if(isset($params['utility']))
      $this->utility = $params['utility'];
    else
      $this->utility = new Utility;

    if(isset($params['url']))
      $this->url = $params['url'];
    else
      $this->url = new Url;

    if(isset($params['user']))
      $this->user = $params['user'];
    else
      $this->user = new User;

    if(isset($params['config']))
      $this->config = $params['config'];
  }


  public function generatePaths($videoName, $dateTaken)
  {
    $ext = substr($videoName, (strrpos($videoName, '.')+1));
    $rootName = preg_replace('/[^a-zA-Z0-9.-_]/', '-', substr($videoName, 0, (strrpos($videoName, '.'))));
    $baseName = sprintf('%s-%s.%s', $rootName, dechex(rand(1000000,9999999)), $ext);
    $originalName = sprintf('%s-%s.%s', $rootName, uniqid(), $ext);
    
    return array(
      'pathOriginal' => sprintf('/video/original/%s/%s', date('Ym', $dateTaken), $originalName),
      'pathBase' => sprintf('/base/%s/%s', date('Ym', $dateTaken), $baseName)
    );
  }

  /**
   * Uploads a new video to the remote file system and database.
   *
   * @param string $localFile The local file system path to the video.
   * @param string $name The file name of the video.
   * @param array $attributes The attributes to save
   * @return mixed string on success, false on failure
   */
  public function upload($localFile, $name, $attributes = array())
  {
    $tagObj = new Tag;

    // check if file type is valid
    if(!$this->isValidMimeType($localFile))
    {
      $this->logger->warn(sprintf('Invalid mime type for %s', $localFile));
      return false;
    }

    $id = $this->user->getNextId('photo');
    if ($id === false)
    {
      $this->logger->crit('Could not fetch next photo ID');
      return false;
    }

    $filenameOriginal = $name;

    $attributes = $this->prepareAttributes($attributes, $localFile, $name);

    $resp = $this->createAndStoreBaseAndOriginal($name, $localFile, $attributes['dateTaken']);
    $attributes = $this->setPathAttributes($attributes, $resp['paths']);

    // check if the underlying file system needs to include any meta data into the db
    $fsExtras = $this->fs->getMetaData($localFile);
    if(!empty($fsExtras))
      $attributes['extraFileSystem'] = $fsExtras;

    if ($resp['status'])
    {
      $this->logger->info("Video ({$id}) successfully stored on the file system");

      if(isset($attributes['tags']) && !empty($attributes['tags']))
        $tagObj->createBatch($attributes['tags']);

      $stored = $this->db->putPhoto($id, $attributes, $attributes['dateTaken']);
      unlink($localFile);
      if($stored)
      {
        $this->logger->info("Video ({$id}) successfully stored to the database");
        return $id;
      }
      else
      {
        $this->logger->warn("Video ({$id}) could NOT be stored to the database");
        return false;
      }
    }

    $this->logger->warn("Video ({$id}) could NOT be stored to the file system");
    return false;
  }

  private function createAndStoreBaseAndOriginal($name, $localFile, $dateTaken)
  {
    $paths = $this->generatePaths($name, $dateTaken);

    // we need to copy the processing thumbnail to a temp location
    $tempFile = tempnam(sys_get_temp_dir(), 'opme-');
    $processingThumbnail = sprintf('%s/assets/images/video-placeholder.jpg', $this->config->paths->docroot);
    copy($processingThumbnail, $tempFile);

    $uploaded = $this->fs->putPhotos(
      array(
        array($localFile => array($paths['pathOriginal'], $dateTaken)),
        array($tempFile => array($paths['pathBase'], $dateTaken))
      )
    );
    
    return array('status' => $uploaded, 'paths' => $paths);
  }
}
