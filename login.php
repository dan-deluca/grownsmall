<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
$formaction = 'login.php';
$smarty->assign('formaction',$formaction);
session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
$givenusername = $mdb2->escape($_POST['username_in']);
$givenpassword = $mdb2->escape($_POST['password_in']);
$update = $_GET['update'];
$smarty->assign('update',$update);
$checkout = $_GET['checkout'];
$smarty->assign('checkout',$checkout);
$username = $mdb2->escape($_POST["username"]);
$smarty->assign('username',$username);
$email = $_POST['email'];
$smarty->assign('email',$email);
$password1 = $mdb2->escape($_POST['password1']);
$password2 = $mdb2->escape($_POST['password2']);
$fname = $mdb2->escape($_POST['fname']);
$smarty->assign('fname',$fname);
$lname = $mdb2->escape($_POST['lname']);
$smarty->assign('lname',$lname);
if (isset($_SESSION["sessionusername"])){
$logged_in = true; 
$smarty->assign('logged_in',$logged_in);
header("Location: http://www.grownsmall.com/account.php");
exit;
}
if (isset($_POST['submit'])){
$sql = "SELECT * from userid where uid LIKE '$givenusername'"; 
$res =& $mdb2->query($sql);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $hashed = $row['password'];
    $username = $row['uid'];
}
if (password_verify($givenpassword, $hashed)) {
     $_SESSION["sessionusername"] = $username;
   $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
   $smarty->assign('username',$username);
   //echo $_POST['checkin'];
   //echo "huh";
   //exit;
   if ($_POST['checkin'] == 'true'){header("Location: http://www.grownsmall.com/checkout.php");
exit;}
else{
header("Location: http://www.grownsmall.com/account.php");
   exit;}
} else {
   $error = valid;
   $smarty->assign('error',$error);
}
}
//if person is trying to login.php/// 
/// ----------------------------///

if (isset($_POST['register_form'])){
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
}else {$mailerr='Please enter a valid email address.';
}
if ($emailnum > 0){
$mailerr = "We allready have that email on file.";}

if (empty($_POST["password1"])){
$passworderr = 'Please provide a password.';
}
elseif ($password1 != $password2) {
$passworderr = "Passwords do not match.";
}

if (isset($nameErr) OR isset($mailerr) OR isset($passworderr)){
$smarty->assign('nameErr',$nameErr);
$smarty->assign('mailerr',$mailerr);
$smarty->assign('passworderr',$passworderr);
$smarty->assign('serror','serror');
$smarty->display('login.tpl'); 
		exit;
}
else{
$hash = password_hash($password1, PASSWORD_BCRYPT);
//echo $hash; 
//exit;
$sql = "INSERT INTO userid (uid,first_name,last_name,email,password) VALUES ('$username','$fname','$lname','$email','$hash')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
///Assign Varibles to send a transactional email///  
$text = "Hello,

My name is Dan DeLuca and along with my wife Lauren we created GrownSmall.  I would like to take this time to welcome you to our site.  Now that you have registered you can shop, make orders and send messages to growers and cooperatives.  A ‘buyers’ account is totally free and requires no further action on your part.  If you would like to sell on the site there are a few easy steps needed to set up you account.  Please check your account homepage. This email is from my personal address so feel free to respond to it if you have any concerns.  Also, please feel free to reach out with suggestions.   We do not spam our users with marketing emails, so follow our blog for the latest updates.  You may want to add the address noreply@grownsmall.com to your address book as that is the account we use to send notifications. Finally, if you enjoy the site please consider sharing it with your friends and following us on Facebook and Twitter.

http://www.facebook.com/grownsmall

http://www.twitter.com/grownsmall

Dan DeLuca
GrownSmall.com
732-977-4326
143 Walnford Rd 
Allentown, NJ 08501";
$htmlmes = "<p>Hello, <br>

My name is Dan DeLuca and along with my wife Lauren we created GrownSmall.  I would like to take this time to welcome you to our site.  Now that you have registered you can shop, make orders and send messages to growers and cooperatives.  A ‘buyers’ account is totally free and requires no further action on your part.  If you would like to sell on the site there are a few easy steps needed to set up you account.  Please check your account homepage. (You also might want to check out this <a href='http://www.grownsmall.com/pricing-table.php'>Pricing Chart</a>)  This email is from my personal address so feel free to respond to it if you have any concerns.  Also, please feel free to reach out with suggestions.   We do not spam our users with marketing emails, so follow our blog for the latest updates.  You may want to add the address noreply@grownsmall.com to your address book as that is the account we use to send notifications. Finally, if you enjoy the site please consider sharing it with your friends and following us on Facebook and Twitter. </p>

<p>www.facebook.com/grownsmall<br>

www.twitter.com/grownsmall</p>
<p>
Dan DeLuca<br>    
GrownSmall.com<br>
732-977-4326<br>
143 Walnford Rd <br>
Allentown, NJ 08501</p>";
$subject = "Thank you for registering with GrownSmall";
$mail = new SendGrid\Email();
$mail->addTo($email)->
       setFrom("dan@grownsmall.com")->
       setFromName('Dan DeLuca')->
       setSubject($subject)->
       setText($text)->
       setHtml($htmlmes);
       $sendgrid->web->send($mail);
$_SESSION['sessionusername'] = $username;
header("Location: http://www.grownsmall.com/account.php?new=true");
$mdb2->disconnect();
	exit;
}
}
$smarty->display('login.tpl');
$mdb2->disconnect();
exit;
?>