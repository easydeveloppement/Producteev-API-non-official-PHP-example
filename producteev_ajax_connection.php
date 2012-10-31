<?php

// ini_set('display_errors','On');

session_start();

require('producteev.php');

$producteev = new producteev_api();

$producteev->loginUser($_POST['login'], $_POST['password']);

?>