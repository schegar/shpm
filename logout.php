<?php

session_start();
unset($_SESSION['userid']);
unset($_SESSION['masterHash']);
session_destroy();
if (isset($_GET['msg'])) {
	header("Location: index.php?msg=" . $_GET['msg']);
} else {
	header("Location: index.php");
}

?>