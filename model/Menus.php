<?php

class TaskException extends Exception { }

class Menus {


  private $_menu_code;
  private $_menu_name;
  private $_domain;
  private $_routerLink;
  private $_server_level;
  

  public function __construct($menu_code, $menu_name, $domain, $routerLink, $server_level) {
    $this->setMenucode($menu_code);
	$this->setMenuname($menu_name);
	$this->setDomain($domain);
	$this->setRouterLink($routerLink);
	$this->setServerLevel($server_level);
    
  }


  public function getMenucode(){
    return $this->_menu_code;
  }
  
  public function getMenuname(){
    return $this->_menu_name;
  }
  
  public function getDomain(){
    return $this->_domain;
  }

  public function getRouterLink(){
    return $this->_routerLink;
  }
  
  public function getServerlevel(){
    return $this->_server_level;
  }
  
  public function setMenucode($menu_code) {

    $this->_menu_code = $menu_code;
  }

  public function setMenuname($menu_name) {

    $this->_menu_name = $menu_name;
  }
  
  public function setDomain($domain) {

    $this->_domain = $domain;
  }
  
  public function setRouterLink($routerLink) {

    $this->_routerLink = $routerLink;
  }
  
  public function setServerLevel($server_level) {

    $this->_server_level = $server_level;
  }
  

  public function returnTaskAsArray() {
    $task = array();
    $task['menu_code'] = $this->getMenucode();
	$task['menu_name'] = $this->getMenuname();
	$task['domain'] = $this->getDomain();
	$task['routerLink'] = $this->getRouterLink();
	$task['server_level'] = $this->getServerlevel();
    return $task;
  }


}

?>
