<?php
require_once __DIR__ . "/../config/db.php";
if(!isset($_SESSION['user'])){ header("Location: index.php"); exit; }
