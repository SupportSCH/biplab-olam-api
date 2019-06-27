<?php

class TaskException extends Exception { }

class User {

  private $_user_id;
  private $_comp_code;
  private $_plant_code;
  private $_user_name;
  private $_login_id;
  private $_department;
  private $_role_code;
  //private $_role_name;


  public function __construct($user_id, $comp_code, $plant_code, $user_name, $login_id, $department, $role_code) {
	$this->setUserID($user_id);
    $this->setCompcode($comp_code);
	$this->setPlantcode($plant_code);
	$this->setUsername($user_name);
	$this->setLoginID($login_id);
	$this->setDepartment($department);
    $this->setRolecode($role_code);
	//$this->setRolename($role_name);
  }
  
  public function getUserID(){
    return $this->_user_id;
  }

  public function getCompcode(){
    return $this->_comp_code;
  }

  public function getPlantcode(){
    return $this->_plant_code;
  }

  public function getUsername() {
    return $this->_user_name;
  }

  public function getLoginID() {
    return $this->_login_id;
  }

  public function getDepartment() {
    return $this->_department;
  }

  public function getRolecode() {
    return $this->_role_code;
  }

  /*public function getRolename() {
    return $this->_role_name;
  }*/

  public function setUserID($user_id) {

    $this->_user_id = $user_id;
  }

  public function setCompcode($comp_code) {

    $this->_comp_code = $comp_code;
  }

  public function setPlantcode($plant_code) {

    $this->_plant_code = $plant_code;
  }

  public function setUsername($user_name) {

    $this->_user_name = $user_name;
  }

  public function setLoginID($login_id) {

    $this->_login_id = $login_id;
  }

  public function setDepartment($department) {

    $this->_department = $department;
  }

  public function setRolecode($role_code) {

    $this->_role_code = $role_code;
  }

  /*public function setRolename($role_name) {

    $this->_role_name = $role_name;
  }*/


  public function returnTaskAsArray() {
    $task = array();
	$task['user_id'] = $this->getUserID();
	$task['comp_code'] = $this->getCompcode();
    $task['plant_code'] = $this->getPlantcode();
	$task['user_name'] = $this->getUsername();
    $task['login_id'] = $this->getLoginID();
	$task['department'] = $this->getDepartment();
	$task['role_code'] = $this->getRolecode();
	//$task['role_name'] = $this->getRolename();
    return $task;
  }


}

?>
