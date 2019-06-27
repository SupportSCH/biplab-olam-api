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

if(empty($_GET)) {
	
	if($_SERVER['REQUEST_METHOD'] === 'POST') {

      try {

        //$query = $readDB->prepare('select id, username, email, password,  deptname, compname, plantname, rolecode from tasks where userid = :userid');
        //$query = $readDB->prepare('select tb1.role_code, tb1.role_name, tb1.plant_code, tb1.menu_code, tb2.menu_name  from tb_m_roles tb1 LEFT OUTER JOIN tb_m_menus tb2 ON tb2.menu_code = tb1.menu_code Group By tb1.menu_code');
		$query = $readDB->prepare('select b.role_code, b.role_name, b.plant_code, b.menu_code, GROUP_CONCAT(a.menu_name) as menu_name from tb_m_roles b INNER JOIN tb_m_menus a ON FIND_IN_SET(a.menu_code, b.menu_code) GROUP BY b.menu_code');
        $query->execute();

        $rowCount = $query->rowCount();

        $taskArray = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new Role($row['role_code'], $row['role_name'], $row['plant_code'], $row['menu_code'], $row['menu_name']);
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