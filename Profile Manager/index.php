<?php
	session_start();
	$department=null;
	if (isset($_GET['dep'])) {
	  $_SESSION['order_department'] = $_GET['dep'];
	  header("refresh:0, url=profile.php");
	}
?>