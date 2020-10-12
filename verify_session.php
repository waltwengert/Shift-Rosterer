<?php
	session_start();
	if(!isset($_SESSION["secret"])){
		echo "not logged in";
	}
?>
