<?php

$host="Localhost";
$bdname="gestion_budget";
$username="root";
$password="";

try{
    $pdo = new PDO ("mysql:host=$host;dbname=$bdname;charset=UTF8", $username, $password);
    $pdo ->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
    //echo "mazyan";
}
catch(PDOexception $e){
    die("Error:".$e->getMessage());
}
?>