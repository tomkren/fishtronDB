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

$handlerID = array_key_exists('id',$_GET) ? $_GET['id'] : '';


function esc ($str) {
  return str_replace ( "\n" , "\\n" , $str );
}

function ret ($str) {
  global $handlerID;
  echo "FishtronDB.handle('".$handlerID."', ". esc($str) .");";  
  exit;
}


switch($_SERVER['REQUEST_METHOD']){

  case 'GET':

    $action = array_key_exists('action',$_GET) ? $_GET['action'] : 'whole';
    
    switch($action){

      case 'get':

        $key = $dbh->quote($_GET['key']);

        $q = "SELECT `val` FROM `big_object` WHERE `key` = $key";

        $result = $dbh->query($q);
        $o = $result->fetchObject();

        if ($o) { 
          ret($o->val); 
        } else {
          ret("undefined");
        }

        break;

      case 'set' :

        $key = $dbh->quote($_GET['key']);
        $val = $dbh->quote($_GET['val']);

        $q = "UPDATE `big_object` SET `val` = $val WHERE `key` =  $key";

        $result = $dbh->query($q);

        // TODO : nerozezná to když tam takovej neni a když tam je ale vkladana hodnota je stejná
        if ($result->rowCount() === 0) {
          $q = "INSERT INTO `big_object` (`key`, `val`) VALUES ($key, $val)";
          $dbh->query($q);
        }

        ret('"OK"');

        break;

      case 'whole' :

        $q = "SELECT * FROM `big_object` WHERE 1;"; 
        $result = $dbh->query($q);

        echo "FishtronDB.handle('".$handlerID."', {\n";
        while ($o = $result->fetchObject()) {
          echo "  ";
          echo $o->key;
          echo " : ";
          echo esc($o->val);
          echo ",\n";
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