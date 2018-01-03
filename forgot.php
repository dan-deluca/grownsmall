<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
if (isset($_GET['resetmail']) AND isset($_GET['code'])){
	$resetmail = $_GET['resetmail'];
	$code = $_GET['code'];
	//echo $resetmail;
	$sql3 = "SELECT email FROM userid WHERE email LIKE '$resetmail' AND TO_SECONDS(userid.date) = '$code'";
	$res =& $mdb2->query($sql3);
	if ($res->numRows() < 1){
		$fatalerror = "<strong>An error occured -- </strong> If you are still having trouble accessing your account please email support directly";
	}
	else {
		$formaction = "forgot.php?resetmail=".$resetmail."&code=".$code;
		$smarty->assign('formaction',$formaction);
		if (isset($_POST['password1']) AND isset($_POST['password2'])){
			$password1 = $_POST['password1'];
			$password2 = $_POST['password2'];
			if (empty($_POST["password1"])){
			$error = 'Please provide a password.';
			}
			elseif ($password1 != $password2) {
			$error = "Passwords did not match.";
			}
			else{
				$hash = password_hash($password1, PASSWORD_BCRYPT);
				$usql = "UPDATE userid SET password = '$hash', userid.date = CURRENT_TIMESTAMP WHERE email = '$resetmail'";
				$affected =& $mdb2->exec($usql);
				$alert = "Your password has been sucessfully reset";
				header("Location: http://www.grownsmall.com/login.php?update=true");
				$mdb2->disconnect();
					exit;

			}			
		}	
	}
	$smarty->assign('alert',$alert);
	$smarty->assign('error',$error);
	$smarty->assign('fatalerror',$fatalerror);
	$smarty->display('forgot2.tpl');
	exit;
}
if (isset($_POST['email'])){
	$email = $mdb2->escape($_POST['email']);
	$sql = "SELECT email, uid, TO_SECONDS(userid.date) AS seconds FROM userid where email LIKE '$email'";
	$res =& $mdb2->query($sql);
	while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$datamail = $row['email'];
	$date = $row['seconds'];
	$userid = $row['uid'];
	}
	if ($res->numRows() < 1){
		$error = "That email was not found in our database";
	}
	else{$valid_user = true;}
}
$smarty->assign('error',$error);
$text = "Hello,

Sorry you have forgotten your password: We can't tell you your old one, but we can help you set a new one.  
 Your user name is;  ".  $userid ."
 
 click or paste into a browser the address below to reset your password. 
 http://grownsmall.com/forgot.php?resetmail=".$datamail."&code=".$date."
 
Dan DeLuca
GrownSmall.com
732-977-4326
143 Walnford Rd 
Allentown, NJ 08501";
$htmlmes = "<p>Hello, <br>

Sorry you have forgotten your password: We can't tell you your old one, but we can help you set a new one.  
Your user name is;".  $userid ."</p>

<p>Click or paste the address below into a browser to reset your password. <br>
<a href='http://grownsmall.com/forgot.php?resetmail=".$datamail."&code=".$date."'>http://grownsmall.com/forgot.php?resetmail=".$datamail."&code=".$date."</a></p>

<p>
Dan DeLuca<br>    
GrownSmall.com<br>
732-977-4326<br>
143 Walnford Rd <br>
Allentown, NJ 08501</p>";

if ($valid_user == true){
	//echo $datamail;
	//exit;
$subject = "Reset your password";
$mail = new SendGrid\Email();
$mail->addTo($datamail)->
	   setFrom("dan@grownsmall.com")->
	   setFromName('Dan DeLuca')->
	   setSubject($subject)->
	   setText($text)->
	   setHtml($htmlmes);
	   $sendgrid->web->send($mail);
  $alert = "A password reset email was sucessfully sent";
  $smarty->assign('alert',$alert);
   }
   $smarty->display('forgot.tpl');
   exit; 
