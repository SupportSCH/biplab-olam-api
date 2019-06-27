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
	
	if($menucode == '' || is_numeric($menucode)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Menu Id can not be blank or must be unique ");
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
			
			$newTask = new Menus(null, (isset($jsonData->menu_name) ? $jsonData->menu_name : null), (isset($jsonData->domain) ? $jsonData->domain : null), (isset($jsonData->routerLink) ? $jsonData->routerLink : null), (isset($jsonData->server_level) ? $jsonData->server_level : null));
			//$menu_code = $newTask->getMenucode();
			$menu_name = $newTask->getMenuname();
			$domain = $newTask->getDomain();
			$routerLink = $newTask->getRouterLink();
			$server_level = $newTask->getServerlevel();
			
			
			
			$query = $writeDB->prepare('insert into tb_m_menus (menu_name, domain, routerLink,  server_level) values(:menu_name, :domain, :routerLink, :server_level)');
			//$query->bindParam(':menu_code', $menu_code, PDO::PARAM_STR);
			$query->bindParam(':menu_name', $menu_name, PDO::PARAM_STR);
			$query->bindParam(':domain', $domain, PDO::PARAM_STR);
			$query->bindParam(':routerLink', $routerLink, PDO::PARAM_STR);
			$query->bindParam(':server_level', $server_level, PDO::PARAM_INT);
			$query->execute();
			
			$rowCount = $query->rowCount();

			if($rowCount === 0) {
			  $response = new Response();
			  $response->setHttpStatusCode(500);
			  $response->setSuccess(false);
			  $response->addMessage("Failed to create Menus");
			  $response->send();
			  exit;
			}
			
			$lastTaskID = $writeDB->lastInsertId();
			
			$query = $writeDB->prepare('select menu_code, menu_name, domain, routerLink, server_level from tb_m_menus where menu_code = :menucode');
			$query->bindParam(':menucode', $lastTaskID, PDO::PARAM_INT);
			$query->execute();
			
			$rowCount = $query->rowCount();

			if($rowCount === 0) {
			  $response = new Response();
			  $response->setHttpStatusCode(500);
			  $response->setSuccess(false);
			  $response->addMessage("Failed to retrieve Menus after creation");
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
			$returnData['task'] = $taskArray;

			$response = new Response();
			$response->setHttpStatusCode(201);
			$response->setSuccess(true);
			$response->addMessage("Menus Created");
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
        $response->addMessage("Failed to insert menus into database - check submitted data for errors");
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