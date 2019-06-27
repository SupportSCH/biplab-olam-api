<?php

require_once('db.php');
require_once('../model/Role.php');
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


if(array_key_exists("rolecode", $_GET)) {
	
	 $rolecode = $_GET['rolecode'];
	
  if($rolecode == '' || is_numeric($rolecode)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Role code can not be blank or must be unique ");
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

      $rolename_updated = false;
      $plantcode_updated = false;
      $menucode_updated = false;
      $menuname_updated = false;
      

      $queryFields = "";

      if(isset($jsonData->role_name)) {
        $rolename_updated = true;
        $queryFields .= "role_name = :role_name, ";
      }

      if(isset($jsonData->plant_code)) {
        $plantcode_updated = true;
        $queryFields .= "plant_code = :plant_code, ";
      }

      if(isset($jsonData->menu_code)) {
        $menucode_updated = true;
        $queryFields .= "menu_code = :menu_code, ";
      }

      if(isset($jsonData->menu_name)) {
        $menuname_updated = true;
        $queryFields .= "menu_name = :menu_name, ";
      }
	  
		
      $queryFields = rtrim($queryFields, ", ");

      if($rolename_updated === false && $plantcode_updated === false && $menucode_updated === false && $menuname_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No role fields are provided");
        $response->send();
        exit;
      }

	  $query = $writeDB->prepare('select b.role_code, b.role_name, b.plant_code, b.menu_code, GROUP_CONCAT(a.menu_name) as menu_name from tb_m_roles b INNER JOIN tb_m_menus a ON FIND_IN_SET(a.menu_code, b.menu_code) where role_code = :rolecode GROUP BY b.menu_code');
      $query->bindParam(':rolecode', $rolecode, PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404); //404- not found
        $response->setSuccess(false);
        $response->addMessage("No roles found to updated");
        $response->send();
        exit;
      }

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new Role($row['role_code'], $row['role_name'], $row['plant_code'], $row['menu_code'], $row['menu_name']);
      }

	  $queryString = "update tb_m_roles set ".$queryFields." where role_code = :rolecode";
      $query = $writeDB->prepare($queryString);

      if($rolename_updated === true) {
        $task->setRolename($jsonData->role_name);
        $up_rolename = $task->getRolename();
        $query->bindParam(':role_name', $up_rolename, PDO::PARAM_STR);
      }

      if($plantcode_updated === true) {
        $task->setPlantcode($jsonData->plant_code);
        $up_plantcode = $task->getPlantcode();
        $query->bindParam(':plant_code', $up_plantcode, PDO::PARAM_INT);
      }

      if($menucode_updated === true) {
        $task->setMenucode($jsonData->menu_code);
        $up_menucode = $task->getMenucode();
        $query->bindParam(':menu_code', $up_menucode, PDO::PARAM_STR);
      }

      if($menuname_updated === true) {
        $task->setMenuname($jsonData->menu_name);
        $up_menuname = $task->getMenuname();
        $query->bindParam(':menu_name', $up_menuname, PDO::PARAM_STR);
      }


      $query->bindParam(':rolecode', $rolecode, PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Roles not updated");
        $response->send();
        exit;
      }

	  $query = $writeDB->prepare('select b.role_code, b.role_name, b.plant_code, b.menu_code, GROUP_CONCAT(a.menu_name) as menu_name from tb_m_roles b INNER JOIN tb_m_menus a ON FIND_IN_SET(a.menu_code, b.menu_code) where role_code = :rolecode GROUP BY b.menu_code');
      $query->bindParam(':rolecode', $rolecode, PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No roles found after updated");
        $response->send();
        exit;
      }

      $taskArray = array();

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $task = new Role($row['role_code'], $row['role_name'], $row['plant_code'], $row['menu_code'], $row['menu_name']);
        $taskArray[] = $task->returnTaskAsArray();
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $taskArray;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Roles updated");
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