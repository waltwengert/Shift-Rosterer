<?php
  function connect(){
    $servername = "localhost";
    $username = "walt";
    $password = "0691174881X";
    $dbname = "project";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password);
    // Check connection
    if (!$conn) {
      echo("Connection failed");
    }

    $db = mysqli_select_db($conn, $dbname);
    if(!$db) {
      die ('Cannot use');
    }

    //echo "Connected successfully";
    return $conn;
  }
?>
