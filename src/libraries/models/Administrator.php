<?php
class Administrator extends BaseModel
{
  public function __construct()
  {
    parent::__construct();
  }

  public function add($email, $host)
  {
    return $this->db->putAdministrator($email, $host); 
  }

  public function delete($email, $host)
  {
    return $this->db->deleteAdministrator($email, $host); 
  }
}
