<?php
session_start();

require 'database.php';

$errors = array();
$p = "server";

// Print clubs list in a select
function select_clubes(){
  if($clubs = consulta_especial("SELECT * FROM clubs")){
    echo '<div class="form-group">
          <label for="clubes">Seleccione su club:</label>
          <select class="form-control" name="prefix">';
    foreach($clubs as $club){
      echo '<option value="'.$club['prefix'].'">'.$club['name'].'</option>';
    }
    echo '</select>
        </div>';
  } else {
    return false;
  }
}

// REGISTER USER
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
  $prefix = mysqli_real_escape_string($db, $_POST['prefix']);
  
  // form validation: ensure that the form is correctly filled ...
  if ($password_1 != $password_2) {	$errors[$p] = PASSDONTMATCH;  }
  
  // if user exists
  if ($user = consulta("users","name='$username' OR email='$email'",MYSQLI_ASSOC,$prefix)){
    if ($user['name'] === $username) { $errors[$p] = USERNAMEEXISTS; }
    if ($user['email'] === $email) { $errors[$p] = EMAILEXISTS;  }
  }

  // Finally, register user if there are no errors in the form
  if (count($errors) == 0) {
    $data['name'] = $username;
    $data['permission'] = 2;
    $data['email'] = $email;
    $data['password'] = md5($password_1);
    $data['forgot_pass_identity'] = md5(uniqid(mt_rand()));
  	if(insertar("users", $data, $prefix)){
    	$modal['title'] = MODALTITLE;
      $modal['body'] = REGISTERED;
      $modal['location'] = "login.php";
      $modal['hidden'] = "false";
    } else {
      $errors[$p] = NOTUPDATED;
    }
  }
}

// LOGIN USER
if (isset($_POST['login_user'])) {
  // receive all input values from the form
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password = md5(mysqli_real_escape_string($db, $_POST['password']));
  $prefix = mysqli_real_escape_string($db, $_POST['prefix']);
  
  // if user exists
  if ($user = consulta("users","email='$email' AND password='$password'", MYSQLI_ASSOC, $prefix)){
    $_SESSION['userid'] = $user['id'];
    $_SESSION['username'] = $user['name'];
    $_SESSION['prefix'] = $prefix; // Prefix from tables for that club
    $_SESSION['permission'] = $user['permission'];
    
    //send to url
    switch($_SESSION['permission']){
      case USER:
        header('location: user.php');
      break;
      case ADMIN:
        header('location: admin.php');
      break;
      case SUPERADMIN:
        header('location: superadmin.php');
      break;
    }
  }else {
    $errors[$p] = WRONGUSERPASS;
  }
}

// FORGOT PASSWORD
if(isset($_POST['forgot_submit'])){
  // receive all input values from the form
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $prefix = mysqli_real_escape_string($db, $_POST['prefix']);
  
  //check whether user exists in the database
  if (!$user = consulta("users", "email='$email'", MYSQLI_ASSOC, $prefix)) $errors[$p] = EMAILNOTFOUND;
  else {
    //update unique string (forgot_pass_identity)
    $data['forgot_pass_identity'] = md5(uniqid(mt_rand()));
    if(!modificar("users", $data, $user['id'], $prefix)) $errors[$p] = IDNOTSAVED;
  }
  // send email to reset password
  if(count($errors)==0){
    $resetPassLink = 'http://crono.rallyrcmadrid.com/reset_password.php?prefix='.$prefix.'&fp_code='.$data['forgot_pass_identity'];
    //send reset password email
    $to = $user['email'];
    $subject = "Password Update Request";
    $mailContent = 'Dear '.$user['name'].', 
    <br/>Recently a request was submitted to reset a password for your account. If this was a mistake, just ignore this email and nothing will happen.
    <br/>To reset your password, visit the following link: <a href="'.$resetPassLink.'">'.$resetPassLink.'</a>
    <br/><br/>Regards,
    <br/>CodexWorld';
    //set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    //additional headers
    $headers .= 'From: RallyRcCrono<info@crono.rallyrcmadrid.com>' . "\r\n";
    //send email
    /*if(mail($to,$subject,$mailContent,$headers)){
      $errors[$p] = "Please check your e-mail, we have sent a password reset link to your registered email");
    } else {
      $errors[$p] = EMAILNOTSENT);
    }*/
    $modal['title'] = MODALTITLE;
    $modal['body'] = CHECKEMAIL;
    $modal['location'] = "forgot-password.php";
    $modal['hidden'] = "false";
  }
}

// RESET PASSWORD 
if(isset($_POST['reset_password']) AND isset($_POST['fp_code']) AND isset($_POST['prefix'])){
  // receive all input values from the form
  $fp_code = mysqli_real_escape_string($db, $_POST['fp_code']);
  $prefix = mysqli_real_escape_string($db,$_POST['prefix']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
  
  // Comprobar que existe el forgot_pass_identity 
  if (!$user = consulta("users","forgot_pass_identity='$fp_code'", MYSQLI_ASSOC, $prefix)) $errors[$p] = WRONGLINK;
  // Comprobar que el password1 y password2 coinciden
  if ($password_1 != $password_2) $errors[$p] = PASSDONTMATCH;
  
  // No hay errores: actualiza password y forgot_pass_identity en la BBDD 
  if (count($errors) == 0) {
    $data['password'] = md5($password_1);
    $data['forgot_pass_identity'] = md5(uniqid(mt_rand()));
    if(modificar("users",$data,$user['id'],$prefix)){
      $modal['title'] = MODALTITLE;
      $modal['body'] = PASSRESETED;
      $modal['location'] = "login.php";
      $modal['hidden'] = "false";
    } else {
      $errors[$p] = NOTUPDATED;
    }
  }
}
?>