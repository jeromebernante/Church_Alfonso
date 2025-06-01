<?php
session_start();
session_destroy();
header("Location: /Church/index.php");
exit();
?>
