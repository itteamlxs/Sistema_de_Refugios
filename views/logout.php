<?php
session_start();
session_unset();
session_destroy();
header('Location: /ads/views/login.php');
exit;
?>