<?php
$host="localhost";
$db="u250286267_clinica";
$user="u250286267_clinica";
$pass="MAJtec7665#";
$charset="utf8mb4";
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(PDOException $e){die("Erro de conexão");}
session_start();
