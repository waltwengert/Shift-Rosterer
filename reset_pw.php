<?php
  session_start();
  include('db.php');

  $id = $_POST["id"];

  //connect to db
  $conn = connect();

  //execute sql
  $sql_s = $conn->prepare("SELECT Email FROM Employee WHERE Employee_ID=?");
  $sql_s->bind_param("s", $id);
  $sql_s->execute();
  $sql_s->bind_result($email);

  //parse result
  $result = [];
  while ($sql_s->fetch()) {
    $result["email"] = $email;
  }

  //generate new password
  $new_pw = "";
  $alpha_num = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

  for ($i=0; $i < 8; $i++) {
    $new_pw .= $alpha_num[rand(0, 61)];
  }

  //email retrieved email with reset password
  $email = $result["email"];
  $msg = "Your password has been reset to: ".$new_pw."\n\nThis is a temporary password. Ask a manager to change it on your next shift.";
  $subject = "Retlaw Reset Password";

  //send email
  mail($email, $subject, $msg);

  //encrypt the password
  $cistrong = true;
  $salt = bin2hex(openssl_random_pseudo_bytes(40,$cistrong));
  $options = [
      'cost'=>12,
      'salt'=> $salt
  ];
  $hashed = password_hash($new_pw,PASSWORD_BCRYPT,$options);

  //update the password in sql
  $sql_u = $conn->prepare("UPDATE Employee SET `Password`=?, Salt=? WHERE Employee_ID=?");
  $sql_u->bind_param("sss", $hashed, $salt, $id);
  $sql_u->execute();

  //alert user their password has been reset and an email has been sent
  $_SESSION["result"] = "An email has been sent.";

  //close connection
  $sql_u->close();
  $sql_s->close();
  $conn->close();

  header("Location: forgot.html");
?>
