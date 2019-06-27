<?php

require_once('db.php');
require_once('../model/createUser.php');
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
	
	if($userid == '' || !is_numeric($userid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("User Id can not be blank or must be unique ");
    $response->send();
    exit;
  }
	
}

elseif (empty($_GET)) {
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
		try{
			
			if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
			  $response = new Response();
			  $response->setHttpStatusCode(400);
			  $response->setSuccess(false);
			  $response->addMessage("Content type header is not set to json");
			  $response->send();
			  exit;
			}
			
			$rowPOSTData = file_get_contents('php://input'); //php:input : it allows you to inspect the body of the request that sent

			if(!$jsonData = json_decode($rowPOSTData)) {
			  $response = new Response();
			  $response->setHttpStatusCode(400);
			  $response->setSuccess(false);
			  $response->addMessage("Request body is not valid JSON");
			  $response->send();
			  exit;
			}
			
			$newTask = new User(null, (isset($jsonData->comp_code) ? $jsonData->comp_code : null), (isset($jsonData->plant_code) ? $jsonData->plant_code : null), (isset($jsonData->user_name) ? $jsonData->user_name : null), (isset($jsonData->login_id) ? $jsonData->login_id : null), (isset($jsonData->department) ? $jsonData->department : null), (isset($jsonData->role_code) ? $jsonData->role_code : null));
			
			$comp_code = $newTask->getCompcode();
			$plant_code = $newTask->getPlantcode();
			$user_name = $newTask->getUsername();
			$login_id = $newTask->getLoginID();
			$department = $newTask->getDepartment();
			$role_code = $newTask->getRolecode();
			//$role_name = $newTask->getRolename();
			
			
			$query = $writeDB->prepare('insert into tb_m_user (comp_code, plant_code, user_name,  login_id, department, role_code) values(:comp_code, :plant_code, :user_name, :login_id, :department, :role_code)');
			//$query = $writeDB->prepare('insert into tb_m_user (comp_code, plant_code, user_name,  login_id, department, role_code, role_name) values(:comp_code, :plant_code, :user_name, :login_id, :department, :role_code, :role_name)
									   //(select tb1.user_id, tb1.comp_code, tb1.plant_code, tb1.user_name,  tb1.login_id, tb1.department, tb1.role_code, tb2.role_name from tb_m_user tb1 LEFT OUTER JOIN tb_m_roles tb2 ON tb2.role_code = tb1.role_code)');
			$query->bindParam(':comp_code', $comp_code, PDO::PARAM_STR);
			$query->bindParam(':plant_code', $plant_code, PDO::PARAM_STR);
			$query->bindParam(':user_name', $user_name, PDO::PARAM_STR);
			$query->bindParam(':login_id', $login_id, PDO::PARAM_STR);
			$query->bindParam(':department', $department, PDO::PARAM_STR);
			$query->bindParam(':role_code', $role_code, PDO::PARAM_STR);
			//$query->bindParam(':role_name', $role_name, PDO::PARAM_STR);
			$query->execute();
			
			$rowCount = $query->rowCount();

			if($rowCount === 0) {
			  $response = new Response();
			  $response->setHttpStatusCode(500);
			  $response->setSuccess(false);
			  $response->addMessage("Failed to create User");
			  $response->send();
			  exit;
			}
			
			$lastTaskID = $writeDB->lastInsertId();
			
			$query = $writeDB->prepare('select tb1.user_id, tb1.comp_code, tb1.plant_code, tb1.user_name,  tb1.login_id, tb1.department, tb1.role_code, tb2.role_name from tb_m_user tb1 LEFT OUTER JOIN tb_m_roles tb2 ON tb2.role_code = tb1.role_code where user_id = :userid');
			$query->bindParam(':userid', $lastTaskID, PDO::PARAM_INT);
			$query->execute();
			
			$rowCount = $query->rowCount();

			if($rowCount === 0) {
			  $response = new Response();
			  $response->setHttpStatusCode(500);
			  $response->setSuccess(false);
			  $response->addMessage("Failed to retrieve User after creation");
			  $response->send();
			  exit;
			}
			
			$taskArray = array();
			
			 while($row = $query->fetch(PDO::FETCH_ASSOC)) {
				  $task = new User($row['user_id'], $row['comp_code'], $row['plant_code'], $row['user_name'], $row['login_id'], $row['department'], $row['role_code'], $row['role_name']);
				  $taskArray[] = $task->returnTaskAsArray();
			}
			
			$returnData = array();
			$returnData['rows_returned'] = $rowCount;
			$returnData['task'] = $taskArray;

			$response = new Response();
			$response->setHttpStatusCode(201);
			$response->setSuccess(true);
			$response->addMessage("Users Created");
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
	  
	   catch(PDOException $ex) {
        error_log("Database query error -".$ex, 0);
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to insert task into database - check submitted data for errors");
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