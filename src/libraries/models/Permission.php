<?php
class Permission extends BaseModel
{
  protected $group, $stored, $user;
  const create = 'C';
  const read = 'R';
  const update = 'U';
  const delete = 'D';

  public function __construct()
  {
    parent::__construct();
    $this->group = new Group;
    $this->user = new User;
  }

  // always default to the highest permission level
  public function allowedAlbums($perm = self::create)
  {
    $permissions = $this->get();
    if(empty($permissions[$perm]))
      return array();
    return $permissions[$perm];
  }

  public function canUpload($to = null)
  {
    // owners/admins can upload
    if($this->user->isAdmin())
      return true;

    $permissions = $this->get();
    // first check if there are ANY upload permissions, if not then return false
    if(!is_array($permissions[self::create]))
      return false;

    // if not to specific album then return true
    if($to === null)
      return true;

    // at the moment $to is a string
    return in_array($to, $permissions[self::create]);
  }

  public function canDelete($to = null)
  {
    // owners/admins can upload
    if($this->user->isAdmin())
      return true;

    $permissions = $this->get();
    // first check if there are ANY upload permissions, if not then return false
    if(!is_array($permissions[self::delete]))
      return false;

    // if not to specific album then return true
    if($to === null)
      return true;

    // at the moment $to is a string
    return in_array($to, $permissions[self::delete]);
  }

  public function get($cache = true)
  {
    if($cache && $this->stored)
      return $this->stored;

    $isAdmin = $this->user->isAdmin();
    $email = $this->user->getEmailAddress();
    $permissions = array(
      self::create => $isAdmin,
      self::read => $isAdmin,
      self::update => $isAdmin,
      self::delete => $isAdmin
    );

    // we only populate if we're not the owner (owner's have full access)
    //  and if there are albums granted to this user
    if(!$isAdmin)
    {
      $groups = $this->db->getGroupsByUser($email);
      if(!empty($groups))
      {
        // if the user is in at least one group then instead of false the default is an empty array
        foreach($permissions as $k => $p)
          $permissions[$k] = array();

        foreach($groups as $group)
        {
          // loop over each album (the key) and it's permissions {C: , R: , U: , D: }
          foreach($group['album'] as $albumId => $perms)
          {
            if(!empty($perms))
            {
              // label is C, R, U, D and the value is null, true, false
              foreach($perms as $label => $value)
              {
                //  we only add the permission if it's true as we default to no permissions
                if($value)
                  $permissions[$label][] = $albumId;
              }
            }
          }
        }
      }
    }

    $this->stored = $permissions;
    return $this->stored;
  }

  public function getCollapsed()
  {
    $permissions = $this->get();
    $res = array();
    foreach($permissions as $key => $perm)
    {
      if(!empty($perm))
        $res[] = $key;
    }

    return $res;
  }
}
