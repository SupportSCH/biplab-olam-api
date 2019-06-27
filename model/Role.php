<?php

class TaskException extends Exception { }

class Role {

  private $_role_code;
  private $_role_name;
  private $_plant_code;
  private $_menu_code;
  private $_menu_name;
  

  public function __construct($role_code, $role_name, $plant_code, $menu_code, $menu_name) {
   
    $this->setRolecode($role_code);
	$this->setRolename($role_name);
	$this->setPlantcode($plant_code);
	$this->setMenucode($menu_code);
	$this->setMenuname($menu_name);
  }


  public function getRolecode() {
    return $this->_role_code;
  }
  
  public function getRolename() {
    return $this->_role_name;
  }
  
  public function getPlantcode() {
    return $this->_plant_code;
  }
  
  public function getMenucode() {
    return $this->_menu_code;
  }
  
  public function getMenuname() {
    return $this->_menu_name;
  }

  public function setRolecode($role_code) {
	  
    $this->_role_code = $role_code;
  }
  
  public function setRolename($role_name) {
	  
    $this->_role_name = $role_name;
  }
  
  public function setPlantcode($plant_code) {
	  
    $this->_plant_code = $plant_code;
  }
  
  public function setMenucode($menu_code) {
	  
    $this->_menu_code = $menu_code;
  }
  
  public function setMenuname($menu_name) {
	  
    $this->_menu_name = $menu_name;
  }
  
  public function returnTaskAsArray() {
    $task = array();
	$task['role_code'] = $this->getRolecode();
	$task['role_name'] = $this->getRolename();
	$task['plant_code'] = $this->getPlantcode();
	$task['menu_code'] = $this->getMenucode();
	$task['menu_name'] = $this->getMenuname();
    return $task;
  }


}

?>
