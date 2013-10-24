<?php
class Plan extends BaseModel
{
  const planFree = 'free';
  const planPro = 'pro';
  const planSilver = 'silver';
  const planGold = 'gold';
  const planPlatinum = 'platinum';

  private $user, $id, $plan;
  public function __construct()
  {
    parent::__construct();
    $this->user = new User;
    $this->id = $this->user->getAttribute('planId');
    if(empty($this->id))
      $this->id = self::typeFree;
  }
  
  public function getAdministratorLimit()
  {
    $plan = $this->get();
    return $plan['limitAdministrators'];
  }
  
  public function getCollaboratorLimit()
  {
    $plan = $this->get();
    return $plan['limitCollaborators'];
  }

  public function get()
  {
    if(!$this->plan)
      $this->plan = $this->db->getPlan($this->id);
    return $this->plan;
  }
}
