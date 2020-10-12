<?php
    session_start();
    include('db.php');

    $username=$_POST["id"];
    $password=$_POST["password"];

    //connect to db
    $conn = connect();

    //execute sql
    $sql = $conn->prepare("SELECT Password, Salt FROM Employee WHERE Employee_ID=?");
    $sql->bind_param("s", $username);
    $sql->execute();
    $sql->bind_result($pass, $salt);

    //parse result
    $result = [];
    $result["pass"] = null;
    while ($sql->fetch()) {
      $result["pass"] = $pass;
    }

    if (password_verify($password, $result["pass"])) {
      $_SESSION["secret"]=uniqid($username,true);
      $_SESSION["user_id"]=$username;
      header('Location: menu.html');
    } else {
      echo "true";
    }
?>
