<?php

require 'config.php';

try {
  $dbh = new PDO(
    'mysql:host=' . $db['hostname'] . ';dbname=' . $db['database'], 
    $db['username'], 
    $db['password']);
}
catch(PDOException $e) {
  die($e->getMessage());
}


$dbh -> exec("SET CHARACTER SET utf8");
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/javascript; charset=utf-8');

//TODO : předělat na bezpečnější skrz nějaký to PDO::prepare nebo jak to je
// např bug: key #zobrazeni dela divnost

function ret ($str) {
  echo "FishtronDB.handle(". $str .");";  
  exit;
}

switch($_SERVER['REQUEST_METHOD']){

  case 'GET':

    $action = array_key_exists('action',$_GET) ? $_GET['action'] : 'whole';
    
    switch($action){

      case 'get':

        $key = $_GET['key'];

        $q = "SELECT `val` FROM `big_object` WHERE `key` = '$key';";

        $result = $dbh->query($q);
        $o = $result->fetchObject();

        if ($o) { 
          ret($o->val); 
        } else {
          ret("undefined");
        }

        break;

      case 'set' :

        $key = $_GET['key'];
        $val = $_GET['val'];

        $q = "UPDATE `big_object` SET `val` = '$val' WHERE `key` =  '$key';";

        $result = $dbh->query($q);

        // TODO : nerozezná to když tam takovej neni a když tam je ale vkladana hodnota je stejná
        if ($result->rowCount() === 0) {
          $q = "INSERT INTO `big_object` (`key`, `val`) VALUES ('$key', '$val');";
          $dbh->query($q);
        }

        ret('"OK"');

        break;

      case 'whole' :

        $q = "SELECT * FROM `big_object` WHERE 1;"; 
        $result = $dbh->query($q);

        echo "FishtronDB.handle({";
        while ($o = $result->fetchObject()) {
          echo $o->key;
          echo ":";
          echo $o->val;
          echo ',';
        }
        echo "});"; exit;

        break;


      default :
        ret('ERROR : unsuported action');
        break;

    }
    break;

  case 'POST':
    ret('ERROR : not yet implemented..');
    break;


}