<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minutes ago
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$delete = $_GET['delete'];
$userid = $_SESSION["sessionusername"];
$formaction = 'inbox.php';
$smarty->assign('formaction',$formaction);
/// IF YOU WANT TO DELETE MESSAGE ////
if (isset($_GET['delete'])){
	$delete_sql = "UPDATE messages SET status = '3' WHERE message_id = '$delete'";
	$affected =& $mdb2->exec($delete_sql);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
}
//echo $userid; 
///-------- If you want add message -------////
if (isset($_POST['letter_sub'])){
	$send_to = $_POST['send_to'];
	///FIND THE EMAIL OF THE USER WE ARE SENDING TO///
	$emailsql = "SELECT email FROM userid WHERE uid = '$send_to'";
	$email_check =& $mdb2->query($emailsql);
	while (($row = $email_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	$send_email = $row['email'];
	}
	$reply_message = $mdb2->escape($_POST['reply_message']);
	$message_id = $_POST['message_id'];
	$new_message = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$userid','$send_to','$reply_message','1')";
	$affected =& $mdb2->exec($new_message);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
	$update_message_status = "UPDATE messages SET status = '2' WHERE message_id = '$message_id'";
	$affected =& $mdb2->exec($update_message_status);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
$message_sent = true;
$smarty->assign('message_sent',$message_sent);
///Assign Varibles to send a transactional email///  
$text = "Hello, 
You have received a message from a user on GrownSmall.  Please log into your Inbox to view and reply to the message.  If you have any trouble with abuse of the messaging system, please immediately contact the GrownSmall team.  

Thank you for using GrownSmall!

GrownSmall.com
732-977-4326
143 Walnford Rd 
Allentown, NJ 08501";
$htmlmes = "<p>Hello, <br>

You have received a message from a user on GrownSmall.  Please log into <a href='http://www.gronwsmall.com/login.php'>your account</a> to view and reply to the message.  If you have any trouble with abuse of the messaging system, please immediately contact the GrownSmall team.  </p>

<p>Thank you for using GrownSmall!</p>
<p>
GrownSmall.com<br>
732-977-4326<br>
143 Walnford Rd <br>
Allentown, NJ 08501</p>";
$subject = "You have a message from a GrownSmall user";
$mail = new SendGrid\Email();
$mail->addTo($send_email)->
	   setFrom("noreply@grownsmall.com")->
	   setSubject($subject)->
	   setText($text)->
	   setHtml($htmlmes);
	   $sendgrid->web->send($mail);
	   $sent = valid;
	   $smarty->assign('sent',$sent);
	  // echo "penis";

}
///------Find all messages send to user -----------/
$sql = "SELECT * FROM messages WHERE sent_to LIKE '$userid' AND status != '3'";
$message_sql =& $mdb2->query($sql);
while (($row = $message_sql->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$message[] = $row;
}
$smarty->assign('message',$message);
$smarty->display('messages.tpl')
?>