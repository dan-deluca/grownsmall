<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
//get variables from form
$formaction = 'newuser.php';
$smarty->assign('formaction',$formaction);
$username = $_POST["username"];
$email = $_POST['email'];
$password1 = $_POST['password1'];
$password2 = $_POST['password2'];
if (isset($_POST['submit'])){
//check username and email for uniqueness 
$sql = "SELECT * FROM userid WHERE email LIKE '$email'";
$emailcheck =& $mdb2->query($sql);
// Always check that result is not an error
if (PEAR::isError($emailcheck)) {
    die($emailcheck->getMessage());
    }
  $emailnum = $emailcheck->numRows();
//exit;
$sql2 = "SELECT * FROM userid WHERE uid LIKE '$username'";
$usernamecheck =& $mdb2->query($sql2);
// Always check that result is not an error
if (PEAR::isError($usernamecheck)) {
    die($usernamecheck->getMessage());
    }
///
  
//echo $usernamecheck->numRows(); 
 // exit;
if (empty($_POST["username"])) {
        $nameErr = "Please provide a username";
    }
    elseif ($usernamecheck->numRows() > 0){
    $nameErr = "That username is allready in use";
    //echo 'stop';
    //exit;
    }
    elseif (preg_match('/^[a-z\d_]{5,20}$/i', $username)) {
    $name = valid; 
} else {
    $nameErr = "Your username is an invalid format, please choose a name between 5 and 20 characters using only letters, number and underscores!";
}
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mailtest = valid;
   //echo $emailnum;
   //exit;
}else {$mailerr='Please enter a valid email address';
}
if ($emailnum > 0){
$mailerr = "We allready have that email on file";}

if (empty($_POST["username"])){
$passworderr = 'Please provide a password';
}
elseif ($password1 != $password2) {
$passworderr = "Passwords Do Not Match";
}

if (isset($nameErr) OR isset($mailerr) OR isset($passworderr)){
$smarty->assign('nameErr',$nameErr);
$smarty->assign('mailerr',$mailerr);
$smarty->assign('passworderr',$passworderr);
$smarty->assign('error','error');
$smarty->display('newuser.tpl'); 
		exit;
}
else{
$hash = password_hash($password1, PASSWORD_BCRYPT);
//echo $hash; 
//exit;
$sql = "INSERT INTO userid (uid,email,password) VALUES ('$username','$email','$hash')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}$mdb2->disconnect();
 header("Location: seller.php");
	exit;
}
}
else{
$smarty->display('newuser.tpl'); 
$mdb2->disconnect();
		exit;
		}
///
///

?>