<?php
error_reporting(E_ALL);
ini_set('display_errors', E_ALL);

include dirname(dirname(__FILE__)) .'/lib/Database.php';
include dirname(dirname(__FILE__)) .'/lib/WAL.php';
$db = Database::getInstance();

if(!empty($_GET['r'])) {
    $app = htmlspecialchars($_GET['r']);
    switch ($app) {
        case 'add':
            if (!empty($_POST['form']['id'])) {
           //     $db->createTable('test', ['id'=>'', 'name' => '', 'title' => '']);
                $db->startTransaction();
                $name =  htmlspecialchars($_POST['form']['name']);
                $title = htmlspecialchars($_POST['form']['title']);
                $id = htmlspecialchars($_POST['form']['id']);
                $db->addEntry('test', ['id' => $id , 'name' => $name, 'title' => $title ]);
                $db->commit();
            }
            break;
        case 'update':
            if(!empty($_GET['id'])){
                if(!empty($_POST['form']['name'])){
                    $name =  htmlspecialchars($_POST['form']['name']);
                    $title = htmlspecialchars($_POST['form']['title']);
                    $id = htmlspecialchars($_GET['id']);
                $db->startTransaction();
                $db->updateEntry('test', ['id' => $id , 'name' => $name, 'title' => $title ]);
                $db->commit();
                }
                else{
                    $data = $db->getEntry('test', $_GET['id']);
                }
            }

            break;
        case 'delete':
            if(!empty($_GET['id'])){
                $db->startTransaction();
                $db->deleteEntry('test', $_GET['id']);
                $db->commit();
            }
            break;
        case 'view' :

            break;

    }
}

/*
$db->startTransaction();
$db->addEntry('hello', ['id' => (int)rand()*100, 'sdfsdfsdfsdf' => 'wefwef', 'ergerg' => 'wefwef']);
$db->addEntry('hello', ['id' => (int)rand()*100, 'sdfsdfsdfrtgsdf' => 'wefrtgwef', 'ergtrgerg' => 'wertgfwef']);
$db->commit(); */
$entries = $db->getTableEntries('test');
$errors = $db->getErrors();
// view
include(dirname(__FILE__). '/../views/index.php');
var_dump($errors);
?>