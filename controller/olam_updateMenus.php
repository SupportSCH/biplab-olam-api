<?php

require_once('db.php');
require_once('../model/Menus.php');
require_once('../model/Response.php');

try {
  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();
}
catch(PDOException $ex) {
  error_log("Connection error -".$ex, 0);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Database Connection error");
  $response->send();
  exit();
}

if(array_key_exists("menucode", $_GET)) {
	
	 $menucode = $_GET['menucode'];
	
  if($menucode == '' || !is_numeric($menucode)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Menu code can not be blank or must be unique ");
    $response->send();
    exit;
  }

  if($_SERVER['REQUEST_METHOD'] === 'PATCH') {

    try{

      if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content type header not set to json");
        $response->send();
        exit;
      }

      $rowPatchData = file_get_contents('php://input');

      if(!$jsonData = json_decode($rowPatchData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body is not valid");
        $response->send();
        exit;
      }

      $menuname_updated = false;
      $domain_updated = false;
      $routerlink_updated = false;
      $serverlevel_updated = false;
      

      $queryFields = "";

      if(isset($jsonData->menu_name)) {
        $menuname_updated = true;
        $queryFields .= "menu_name = :menu_name, ";
      }

      if(isset($jsonData->domain)) {
        $domain_updated = true;
        $queryFields .= "domain = :domain, ";
      }

      if(isset($jsonData->routerLink)) {
        $routerlink_updated = true;
        $queryFields .= "routerLink = :routerLink, ";
      }

      if(isset($jsonData->server_level)) {
        $serverlevel_updated = true;
        $queryFields .= "server_level = :server_level, ";
      }
	  
		
      $queryFields = rtrim($queryFields, ", ");

      if($menuname_updated === false && $domain_updated === false && $routerlink_updated === false && $serverlevel_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No menus fields are provided");
        $response->send();
        exit;
      }

	  $query = $writeDB->prepare('select menu_code, menu_name, domain, routerLink, server_level from tb_m_menus where menu_code = :menucode');
      $query->bindParam(':menucode', $menucode, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404); //404- not found
        $response->setSuccess(false);
        $response->addMessage("No menus found to updated");
        $response->send();
        exit;
      }

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new Menus($row['menu_code'], $row['menu_name'], $row['domain'], $row['routerLink'], $row['server_level']);
      }

	  $queryString = "update tb_m_menus set ".$queryFields." where menu_code = :menucode";
      $query = $writeDB->prepare($queryString);

      if($menuname_updated === true) {
        $task->setMenuname($jsonData->menu_name);
        $up_menuname = $task->getMenuname();
        $query->bindParam(':menu_name', $up_menuname, PDO::PARAM_STR);
      }

      if($domain_updated === true) {
        $task->setDomain($jsonData->domain);
        $up_domain = $task->getDomain();
        $query->bindParam(':domain', $up_domain, PDO::PARAM_STR);
      }

      if($routerlink_updated === true) {
        $task->setRouterLink($jsonData->routerLink);
        $up_routerLink = $task->getRouterLink();
        $query->bindParam(':routerLink', $up_routerLink, PDO::PARAM_STR);
      }

      if($serverlevel_updated === true) {
        $task->setServerLevel($jsonData->server_level);
        $up_serverlevel = $task->getServerlevel();
        $query->bindParam(':server_level', $up_serverlevel, PDO::PARAM_INT);
      }


      $query->bindParam(':menucode', $menucode, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Menus not updated");
        $response->send();
        exit;
      }

	  $query = $writeDB->prepare('select menu_code, menu_name, domain, routerLink, server_level from tb_m_menus where menu_code = :menucode');
      $query->bindParam(':menucode', $menucode, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No menus found after updated");
        $response->send();
        exit;
      }

      $taskArray = array();

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $task = new Menus($row['menu_code'], $row['menu_name'], $row['domain'], $row['routerLink'], $row['server_level']);
        $taskArray[] = $task->returnTaskAsArray();
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $taskArray;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Menus updated");
      $response->setData($returnData);
      $response->send();
      exit;
    }

    catch(TaskException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;

    }
    

  }
  else {
     $response = new Response();
     $response->setHttpStatusCode(405);
     $response->setSuccess(false);
     $response->addMessage("Request method is not allowed");
     $response->send();
     exit;
  }

	
}

else {
   $response = new Response();
   $response->  setHttpStatusCode(404);
   $response->setSuccess(false);
   $response->addMessage("Endpoint not found");
   $response->send();
   exit;
}


?>