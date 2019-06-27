<?php

require_once('db.php');
require_once('../model/User.php');
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

if(array_key_exists("userid", $_GET)) {
	
	$userid = $_GET['userid'];
	
  if($userid == '' || !is_numeric($userid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("User Id can not be blank or must be unique ");
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

      $compcode_updated = false;
      $plantcode_updated = false;
      $username_updated = false;
      $loginid_updated = false;
      $department_updated = false;
      $rolecode_updated = false;
      $rolename_updated = false;

      $queryFields = "";

      if(isset($jsonData->comp_code)) {
        $compcode_updated = true;
        $queryFields .= "comp_code = :comp_code, ";
      }

      if(isset($jsonData->plant_code)) {
        $plantcode_updated = true;
        $queryFields .= "plant_code = :plant_code, ";
      }

      if(isset($jsonData->user_name)) {
        $username_updated = true;
        $queryFields .= "user_name = :user_name, ";
      }

      if(isset($jsonData->login_id)) {
        $loginid_updated = true;
        $queryFields .= "login_id = :login_id, ";
      }

      if(isset($jsonData->department)) {
        $department_updated = true;
        $queryFields .= "department = :department, ";
      }

      if(isset($jsonData->role_code)) {
        $rolecode_updated = true;
        $queryFields .= "role_code = :role_code, ";
      }
	
	  
      if(isset($jsonData->role_name)) {
        $rolename_updated = true;
        $queryFields .= "role_name = :role_name, ";
      }
	  
		
      $queryFields = rtrim($queryFields, ", ");

      if($compcode_updated === false && $plantcode_updated === false && $username_updated === false && $loginid_updated === false && $department_updated === false && $rolecode_updated === false && $rolename_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("No user fields are provided");
        $response->send();	
        exit;
      }

	  $query = $writeDB->prepare('select tb1.user_id, tb1.comp_code, tb1.plant_code, tb1.user_name,  tb1.login_id, tb1.department, tb1.role_code, tb2.role_name from tb_m_user tb1 LEFT OUTER JOIN tb_m_roles tb2 ON tb2.role_code = tb1.role_code where user_id = :userid');
	  //$query = $writeDB->prepare('select user_id, comp_code, plant_code, user_name,  login_id, department, role_code, role_name from tb_m_user where user_id = :userid');
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404); //404- not found
        $response->setSuccess(false);
        $response->addMessage("No User found to updated");
        $response->send();
        exit;
      }

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new User($row['user_id'], $row['comp_code'], $row['plant_code'], $row['user_name'], $row['login_id'], $row['department'], $row['role_code'], $row['role_name']);
      }

	  
	  $queryString = "update tb_m_user  set ".$queryFields." where user_id = :userid";
	  $query = $writeDB->prepare($queryString);

      if($compcode_updated === true) {
        $task->setCompcode($jsonData->comp_code);
        $up_compcode = $task->getCompcode();
        $query->bindParam(':comp_code', $up_compcode, PDO::PARAM_STR);
      }

      if($plantcode_updated === true) {
        $task->setPlantcode($jsonData->plant_code);
        $up_plantcode = $task->getPlantcode();
        $query->bindParam(':plant_code', $up_plantcode, PDO::PARAM_STR);
      }

      if($username_updated === true) {
        $task->setUsername($jsonData->user_name);
        $up_username = $task->getUsername();
        $query->bindParam(':user_name', $up_username, PDO::PARAM_STR);
      }

      if($loginid_updated === true) {
        $task->setLoginID($jsonData->login_id);
        $up_loginid = $task->getLoginID();
        $query->bindParam(':login_id', $up_loginid, PDO::PARAM_STR);
      }

      if($department_updated === true) {
        $task->setDepartment($jsonData->department);
        $up_department = $task->getDepartment();
        $query->bindParam(':department', $up_department, PDO::PARAM_STR);
      }

      if($rolecode_updated === true) {
        $task->setRolecode($jsonData->role_code);
        $up_rolecode = $task->getRolecode();
        $query->bindParam(':role_code', $up_rolecode, PDO::PARAM_STR);
      }
	  
	  if($rolename_updated === true) {
        $task->setRolename($jsonData->role_name);
        $up_rolename = $task->getRolename();
        $query->bindParam(':role_name', $up_rolename, PDO::PARAM_STR);
      }


      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("User not updated");
        $response->send();
        exit;
      }

	  $query = $writeDB->prepare('select tb1.user_id, tb1.comp_code, tb1.plant_code, tb1.user_name,  tb1.login_id, tb1.department, tb1.role_code, tb2.role_name from tb_m_user tb1 LEFT OUTER JOIN tb_m_roles tb2 ON tb2.role_code = tb1.role_code where user_id = :userid');
	  //$query = $writeDB->prepare('select user_id, comp_code, plant_code, user_name,  login_id, department, role_code, role_name from tb_m_user where user_id = :userid');
      $query->bindParam(':userid', $userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("No user found after updated");
        $response->send();
        exit;
      }

      $taskArray = array();

      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $task = new User($row['user_id'], $row['comp_code'], $row['plant_code'], $row['user_name'], $row['login_id'], $row['department'], $row['role_code'], $row['role_name']);
        $taskArray[] = $task->returnTaskAsArray();
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['tasks'] = $taskArray;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("User updated");
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