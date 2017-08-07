<?php

session_start();

$_SESSION['user_id'] = -1;

session_destroy();

header('Location: ./login.php');
die();

?>