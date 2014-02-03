<?php
/**
  * Album controller for API endpoints
  *
  * @author Jaisen Mathai <jaisen@jmathai.com>
  */
class ApiAlbumController extends ApiBaseController
{
  /**
    * Call the parent constructor
    *
    * @return void
    */
  public function __construct()
  {
    parent::__construct();
    $this->album = new Album;
    $this->user = new User;
  }

  public function coverUpdate($albumId, $photoId)
  {
    // check if a token is passed in
    // if a token is passed we check the type and the data to make sure the permissions are correct
    // else use default authentication
    $token = null;
    $validatedToken = false;
    if(isset($_POST['token']) && !empty($_POST['token']))
    {
      $shareTokenObj = new ShareToken;
      $tokenArr = $shareTokenObj->get($_POST['token']);
      if(empty($tokenArr) || $tokenArr['type'] != 'upload' || $albumId !== $tokenArr['data'])
        return $this->forbidden('No permission to add photo to album with the passed in token', false);
      $token = $tokenArr['id'];
      $validatedToken = true;
    }
    else
    {
      getAuthentication()->requireAuthentication(array(Permission::create), array($albumId));
      getAuthentication()->requireCrumb();
    }

    $tokenUrl = '';
    if(!empty($token))
      $tokenUrl = sprintf('/token-%s', $token);
    $photoResp = $this->api->invoke(sprintf('/photo/%s%s/view.json', $photoId, $tokenUrl), EpiRoute::httpGet, array('_GET' => array('generate' => 'true', 'returnSizes' => '100x100,100x100xCR,200x200,200x200xCR')));
    if($photoResp['code'] === 200)
    {
      // TODO: this clobbers anything that was in `extra` (currently nothing)
      $status = $this->album->update($albumId, array('extra' => array('cover' => $photoResp['result'])));
      if($status)
        return $this->success('Your album cover was updated.', true);
    }
    return $this->error('Sorry, your album cover could not be updated.', false);
  }

  public function create()
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();

    // disallow duplicate names for album
    //  return existing album if attempting to create
    //  gh-911
    $albumNameCheck = $this->album->getAlbumByName($_POST['name']);
    if(!empty($albumNameCheck))
    {
      $albumResp = $this->api->invoke("/album/{$albumNameCheck['id']}/view.json", EpiRoute::httpGet);
      if($albumResp['code'] === 200)
        return $this->conflict('Album name exists', $albumResp['result']);
      else
        return $this->error('Album with the same name already exists but failed to fetch it', false);
    }

    $albumId = $this->album->create($_POST);
    if($albumId)
    {
      $albumResp = $this->api->invoke("/{$this->apiVersion}/album/{$albumId}/view.json", EpiRoute::httpGet);
      if($albumResp['code'] == 200)
      {
        $this->plugin->setData('album', $albumResp['result']);
        $this->plugin->setData('albumId', $albumId);
        $this->plugin->invoke('onAlbumCreated');

        return $this->created('Album created', $albumResp['result']);
      }
    }
    return $this->error('Could not add album', false);
  }

  public function delete($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $status = $this->album->delete($id);
    if($status)
      return $this->noContent('Album was deleted', true);

    return $this->error('Could not delete album', false);
  }

  public function form()
  {
    $template = $this->theme->get('partials/album-form.php');
    return $this->success('Album form', array('markup' => $template));
  }

  public function inviteUploaders()
  {
    $albumId = $_GET['id'];
    $albumResp = $this->api->invoke(sprintf('/album/%s/view.json', $albumId), EpiRoute::httpGet);
    if($albumResp['code'] !== 200)
      return $this->error('Could not retrieve album', false);

    $album = $albumResp['result'];

    $sharingTokenResp = $this->api->invoke(sprintf('/token/upload/%s/create.json', $albumId), EpiRoute::httpPost, array('_POST' => array('dateExpires' => strtotime('+8 weeks'))));

    $token = null;
    if($sharingTokenResp['code'] === 200 || $sharingTokenResp['code'] === 201)
      $token = $sharingTokenResp['result'];

    if($token)
    {
      $template = $this->theme->get('partials/album-invite-uploaders.php', array('albumId' => $albumId, 'album' => $album, 'token' => $token));
      return $this->success('Invite uploaders', array('markup' => $template));
    }

    return $this->error('Could not generate upload token', false);
  }

  public function list_()
  {
    $permissionObj = new Permission;
    $email = $this->user->getEmailAddress();
    $pageSize = $this->config->pagination->albums;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if(isset($_GET['pageSize']))
      $pageSize = (int)$_GET['pageSize'];

    $offset = ($pageSize * $page) - $pageSize;
    // model passes on the email
    $albums = $this->album->getAlbums($email, $pageSize, $offset);
    if($albums === false)
      return $this->error('Could not retrieve albums', false);

    $skipEmpty = isset($_GET['skipEmpty']) && $_GET['skipEmpty'] == 1 ? 1 : 0;
    $totalRows = $albums[0]['totalRows'];

    // If the request is authenticated AND the user is not an admin AND has access to > 1 album then we have to descend into groups for permissions
    // Else we just leave the albums as is and pull counts based on the appropriate column
    if(getAuthentication()->isRequestAuthenticated() && !$this->user->isAdmin() && count($permissionObj->allowedAlbums()))
    {
      $permission = Permission::read;
      if(isset($_GET['permission']))
        $permission = $_GET['permission'];

      $permissionObj = new Permission;
      $allowedAlbums = $permissionObj->allowedAlbums($permission);
      foreach($albums as $key => $alb)
      {
        // if an album has no public photos then we check to see if this user has permission to view it
        if(($skipEmpty && $alb['countPublic'] == 0) || !in_array($alb['id'], $allowedAlbums))
        {
          unset($albums[$key]);
          $totalRows--;
        }
        else
        {
          if($skipEmpty && $alb['countPublic'] == 0)
          {
            unset($albums[$key]);
            $totalRows--;
          }
          else
          {
            $albums[$key]['count'] = $alb['countPublic'];
            unset($albums[$key]['countPublic'], $albums[$key]['countPrivate']);
          }
        }
      }
    }
    else
    {
      $albumCountKey = $this->user->isAdmin() ? 'countPrivate' : 'countPublic';
      foreach($albums as $key => $val)
      {
        if($skipEmpty && $val[$albumCountKey] == 0)
        {
          unset($albums[$key]);
          $totalRows--;
        }
        else
        {
          $albums[$key]['count'] = $val[$albumCountKey];
          unset($albums[$key]['countPublic'], $albums[$key]['countPrivate']);
        }
      }
    }

    if(!empty($albums))
    {
      // since we might have removed elements we need to rekey $albums
      $albums = array_values($albums);
      $albums[0]['totalRows'] = $totalRows;

      if(!empty($albums))
      {
        $albums[0]['currentPage'] = intval($page);
        $albums[0]['currentRows'] = count($albums);
        $albums[0]['pageSize'] = intval($pageSize);
        $albums[0]['totalPages'] = !empty($pageSize) ? ceil($albums[0]['totalRows'] / $pageSize) : 0;
      }
    }

    return $this->success('List of albums', $albums);
  }

  public function updateIndex($albumId, $type, $action)
  {
    // check if a token is passed in
    // if a token is passed we check the type and the data to make sure the permissions are correct
    // else use default authentication
    $token = null;
    $validatedToken = false;
    if(isset($_POST['token']) && !empty($_POST['token']))
    {
      $shareTokenObj = new ShareToken;
      $tokenArr = $shareTokenObj->get($_POST['token']);
      if(empty($tokenArr) || $tokenArr['type'] != 'upload' || $albumId !== $tokenArr['data'])
        return $this->forbidden('No permission to add photo to album with the passed in token', false);
      $token = $tokenArr['id'];
      $validatedToken = true;
    }
    else
    {
      getAuthentication()->requireAuthentication(array(Permission::create), array($albumId));
      getAuthentication()->requireCrumb();
    }

    $this->logger->info(sprintf('Calling ApiAlbumController::updateIndex with %s, %s, %s', $albumId, $type, $action));

    if(!isset($_POST['ids']) || empty($_POST['ids']))
      return $this->error('Please provide ids', false);

    switch($action)
    {
      case 'add':
        $resp = $this->album->addElement($albumId, $type, $_POST['ids']);
        if($resp)
        {
          $album = $this->album->getAlbum($albumId, false, null, $validatedToken);
          if(empty($album['cover']))
          {
            $ids = (array)explode(',', $_POST['ids']);
            $id = array_pop($ids);
            // TODO do we need to pass the token in here?
            $this->api->invoke("/album/{$albumId}/cover/{$id}/update.json", EpiRoute::httpPost, array('_POST' => array('token' => $token)));
          }
        }
        break;
      case 'remove':
        $resp = $this->album->removeElement($albumId, $type, $_POST['ids']);
        $album = $this->album->getAlbum($albumId);
        $photoIdsArr = (array)explode(',', $_POST['ids']);
        if(isset($album['cover']['id']) && in_array($album['cover']['id'], $photoIdsArr))
        {
          // TODO: this clobbers anything that was in `extra` (currently nothing)
          $this->album->update($albumId, array('extra' => array('cover' => null)));
        }
        break;
    }

    if(!$resp)
      return $this->error('All items were not updated', false);

    return $this->success('All items updated', true);
  }

  public function update($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();

    $status = $this->album->update($id, $_POST);
    if(!$status)
      return $this->error('Could not update album', false);

    $albumResp = $this->api->invoke("/{$this->apiVersion}/album/{$id}/view.json", EpiRoute::httpGet);
    return $this->success('Album {$id} updated', $albumResp['result']);
  }

  public function view($id)
  {
    $includeElements = $validatedToken = false;
    if(isset($_GET['includeElements']) && $_GET['includeElements'] == '1')
      $includeElements = true;

    $token = null;
    if(isset($_GET['token']) && !empty($_GET['token']))
    {
      $shareTokenObj = new ShareToken;
      $tokenArr = $shareTokenObj->get($_GET['token']);
      // make sure the token isn't empty and that it's ID matches this id
      if(!empty($tokenArr) && $id == $tokenArr['data'] && ($tokenArr['type'] == 'album' || $tokenArr['type'] == 'upload'))
        $validatedPermission = true;
    }
    else
    {
      $permissionObj = new Permission;
      $validatedPermission = $permissionObj->canUpload($id);
    }

    $album = $this->album->getAlbum($id, $includeElements, null, $validatedPermission);
    if($album === false)
      return $this->error('Could not retrieve album', false);

    $albumCountKey = $this->user->isAdmin() ? 'countPrivate' : 'countPublic';
    $album['count'] = $album[$albumCountKey];
    unset($album['countPublic'], $album['countPrivate']);

    return $this->success('Album', $album);
  }
}
