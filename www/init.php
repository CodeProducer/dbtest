<?php
error_reporting(E_ALL);
ini_set('display_errors', E_ALL);

include dirname(dirname(__FILE__)) .'/lib/Database.php';
include dirname(dirname(__FILE__)) .'/lib/WAL.php';
$db = Database::getInstance();
$db->createTable('test', ['id'=>'', 'name' => '', 'title' => '']);
if(empty($db->getErrors())){
    echo 'table successfully created';
}