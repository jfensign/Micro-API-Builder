<?php

class UserModel extends Model {

  protected $db;

  public function __construct() {
    $this->db = App::load()->database();
  }

}
?>