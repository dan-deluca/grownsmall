<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
// connect to the MySQL database with the zip code table
require_once 'config.php';
$formaction = 'contact.php';
$smarty->assign('formaction',$formaction);
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$subject = $_POST['subject'];
$comments = $_POST['comments'];
$verify = $_POST['verify'];
$dan_mail = 'dan@grownsmall.com';
//exit;
///--- send message ---> 
if (isset($_POST['send_message']) AND $verify == 4){
$mail = new SendGrid\Email();
$mail->addTo($dan_mail)->
       setFrom($email)->
       setSubject($subject)->
       setText("A message from '$name' --- '$comments' -- Phone at '$phone'")->
       setHtml("<strong>A message from '$name'</strong><br><br> '$comments'<br><hr> Phone at: <strong>'$phone'</strong>");
       $sendgrid->web->send($mail);
       
       $sent = valid;
       $smarty->assign('sent',$sent);
      // echo "penis";
      // exit;
}


$smarty->display('contact.tpl');
exit;
?>