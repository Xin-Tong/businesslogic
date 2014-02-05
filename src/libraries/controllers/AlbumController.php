<?php
/**
  * Album controller for HTML endpoints
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
  */
class AlbumController extends BaseController
{
  public function __construct()
  {
    parent::__construct();
  }

  public function download($id)
  {
    getAuthentication()->requireAuthentication();
    $albumResp = $this->api->invoke(sprintf('/album/%s/view.json', $id));
    $album = $albumResp['result'];
    if($albumResp['code'] !== 200)
    {
      $this->route->run('/error/404');
      return;
    }

    $photoObj = new Photo;
    $photosRespParams = $_GET;
    $photosRespParams['pageSize'] = 0;
    $photosResp = $this->api->invoke(sprintf('/photos/album-%s/list.json', $id), EpiRoute::httpGet, array('_GET' => $photosRespParams));
    $result = $photosResp['result'];

    if($photosResp['code'] !== 200 && empty($result))
    {
      $this->route->run('/error/500');
      return;
    }

    $directoryName = preg_replace('/\W/', ' ', $album['name']);

    $downloadList = array();
    foreach($result as $photo)
    {
      if(isset($photo['video']) && !empty($photo['video']))
        continue;

      $downloadList[] = $photo;
    }

    if(count($downloadList) === 0)
    {
      $this->route->run('/error/500');
      return;
    }

    $zip = new ZipStream(sprintf('%s.zip', $directoryName));
    foreach($downloadList as $dl)
    {

      $fp = $photoObj->getDownloadPointer($dl);
      if(!$fp)
        continue;

      $zip->addLargeFile($fp, sprintf('%s/%s', $directoryName, $dl['filenameOriginal']));
      fclose($fp);
    }
    
    return $zip->finalize();
  }

  public function list_()
  {
    $userObj = new User;
    $albumObj = new Album;
    $page = 1;
    $pageSize = null;
    if(isset($_GET['pageSize']))
      $pageSize = (int)$_GET['pageSize'];
    if(isset($_GET['page']))
      $page = (int)$_GET['page'];

    $permissionObj = new Permission;
    $albumsResp = $this->api->invoke('/albums/list.json', EpiRoute::httpGet, array('_GET' => array('page' => $page, 'pageSize' => $pageSize, 'skipEmpty' => $albumObj->skipEmptyValue())));
    $albums = $albumsResp['result'];
    $this->plugin->setData('albums', $albums);
    $this->plugin->setData('page', 'albums');
    $body = $this->theme->get($this->utility->getTemplate('albums.php'), array('albums' => $albums));
    $this->theme->display($this->utility->getTemplate('template.php'), array('body' => $body, 'page' => 'albums'));
  }
}
