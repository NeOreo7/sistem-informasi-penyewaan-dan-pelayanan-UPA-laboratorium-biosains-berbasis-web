<?php
require_once 'session.php';
$session = new Session();
$session->destroy();
header("Location: signin.php");
exit;
?>