<?php
class Plan extends BaseModel
{
  const planFree = 'free';
  const planPro = 'pro';
  const planSilver = 'silver';
  const planGold = 'gold';
  const planPlatinum = 'platinum';

  const defaultLimitAdministrators = 4;
  const defaultLimitCollaborators = 5;

  private $user, $id, $plan;
  public function __construct()
  {
    parent::__construct();
    $this->user = new User;
    $this->id = $this->user->getAttribute('planId');
    if(empty($this->id))
      $this->id = self::planFree;
  }
  
  public function getAdministratorLimit()
  {
    $plan = $this->get();
    if(!isset($plan['limitAdministrators']))
      return self::defaultLimitAdministrators;
    return $plan['limitAdministrators'];
  }
  
  public function getCollaboratorLimit()
  {
    $plan = $this->get();
    if(!isset($plan['limitCollaborators']))
      return self::defaultLimitCollaborators;
    return $plan['limitCollaborators'];
  }

  public function get()
  {
    if(!$this->plan)
      $this->plan = $this->db->getPlan($this->id);
    return $this->plan;
  }
}
