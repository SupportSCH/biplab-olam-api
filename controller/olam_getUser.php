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

if(empty($_GET)) {

	if($_SERVER['REQUEST_METHOD'] === 'POST') {

      try {

        //$query = $readDB->prepare('select user_id, comp_code, plant_code, user_name,  login_id, department, role_code, role_name from tb_m_user');
        $query = $readDB->prepare('select tb1.user_id, tb1.comp_code, tb1.plant_code, tb1.user_name,  tb1.login_id, tb1.department, tb1.role_code, tb2.role_name from tb_m_user tb1 LEFT OUTER JOIN tb_m_roles tb2 ON tb2.role_code = tb1.role_code');
        $query->execute();

        $rowCount = $query->rowCount();

        $taskArray = array();

        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
          $task = new User($row['user_id'], $row['comp_code'], $row['plant_code'], $row['user_name'], $row['login_id'], $row['department'], $row['role_code'], $row['role_name']);
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
