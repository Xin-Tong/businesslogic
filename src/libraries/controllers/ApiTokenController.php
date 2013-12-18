<?php
class ApiTokenController extends ApiController
{
  private $token;

  public function __construct()
  {
    parent::__construct();
    $this->token = new Token;
  }

  public function create($type, $data)
  {
    // if trying to create an album token then we check if the user has permission
    // if trying to create a photo token then we check against the API for permissions
    // TODO #1403 clean up the else case
    if($type == 'album' || $type == 'upload')
    {
      getAuthentication()->requireAuthentication(array('C'), $data);
    }
    else
    {
      getAuthentication()->requireAuthentication(array('C'));
      $checkPhotoPerms = $this->api->invoke(sprintf('/photo/%s/view.json', $data), EpiRoute::httpGet);
      if($checkPhotoPerms['code'] !== 200)
        OPException::raise(new OPAuthorizationPermissionException('No access to create a sharing token for this photo'));
    }
    getAuthentication()->requireCrumb();

    $params = $_POST;
    $params['type'] = $type;
    $params['data'] = $data;

    // gh-1414 check if at least one token exists
    //  we originally allowed multiple so we have to get this as an array
    $tok = $this->token->getByTarget($type, $data);
    if(count($tok) > 0)
      return $this->success('Token exists, returning it', $tok[0]);

    $id = $this->token->create($params);
    if($id === false)
      return $this->error('Could not create share token', false);

    $utilityObj = new Utility;
    $tok = $this->token->get($id);
    $tok['link'] = sprintf('%s://%s/photos/upload/%s', $utilityObj->getProtocol(false), $utilityObj->getHost(), $tok['id']);
    return $this->created('Successfully created share token', $tok);
  }

  public function delete($id)
  {
    getAuthentication()->requireAuthentication();
    getAuthentication()->requireCrumb();
    $res = $this->token->delete($id);
    if($res === false)
      return $this->error('Could not delete share token', false);

    return $this->noContent('Successfully deleted share token', true);
  }

  public function list_()
  {
    $tokens = $this->token->getAll();
    if($tokens === false)
      return $this->error('Error getting sharing tokens', false);

    $retval = array('photos' => array(), 'albums' => array());
    foreach($tokens as $token)
    {
      if($token['type'] === 'photo')
        $retval['photos'][] = $token;
      else
        $retval['albums'][] = $token;
    }
    return $this->success('Share tokens', $retval);
  }

  public function listByTarget($type, $data)
  {
    $tokens = $this->token->getByTarget($type, $data);
    if($tokens === false)
      return $this->error('Error getting sharing tokens', false);

    return $this->success('Share tokens by target', $tokens);
  }

  public function view($id)
  {
    $tok = $this->token->get($id);
    if($tok === false)
      return $this->error('Could not get valid sharing token', false);

    return $this->success('Successfully fetched sharing token', $tok);
  }
}
