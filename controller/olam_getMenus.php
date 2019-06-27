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

if(empty($_GET)) {
	
	if($_SERVER['REQUEST_METHOD'] === 'POST') {

      try {

        //$query = $readDB->prepare('select id, username, email, password,  deptname, compname, plantname, rolecode from tasks where userid = :userid');
        $query = $readDB->prepare('select menu_code, menu_name, domain, routerLink, server_level from tb_m_menus');
        $query->execute();

        $rowCount = $query->rowCount();

        $taskArray = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new Menus($row['menu_code'], $row['menu_name'], $row['domain'], $row['routerLink'], $row['server_level']);
          $taskArray[] = $task->returnTaskAsArray();
        }

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['tasks'] = $taskArray;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;
      }
      catch(TaskException $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
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
        $response->addMessage("Failed to get data");
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