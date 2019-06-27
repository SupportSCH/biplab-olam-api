<?php

class DB {
   // Start the session
    private static $writeDBConnection;
    private static $readDBConnection;

    public static function connectWriteDB() {
      if(self::$writeDBConnection === null) {
        //self::$writeDBConnection = new PDO('mysql:host=172.16.16.161;dbname=sch_mes_oks', 'adminqc','qcadmin');
		self::$writeDBConnection = new PDO('mysql:host=localhost;dbname=sch_mes_oks', 'root','');
        self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
      }
	  
      return self::$writeDBConnection;
    }
	
    public static function connectReadDB() {
      if(self::$readDBConnection === null) {
        //self::$readDBConnection = new PDO('mysql:host=172.16.16.161;dbname=sch_mes_oks', 'adminqc','qcadmin');
		self::$readDBConnection = new PDO('mysql:host=localhost;dbname=sch_mes_oks', 'root','');
        self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
      }

      return self::$readDBConnection;
    }

}


 ?>
