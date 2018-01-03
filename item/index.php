<?php
//test of save
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once '../config.php';
$formaction = 'index.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
session_start();
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minutes ago
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
$userid = $_SESSION["sessionusername"];
$smarty->assign('userid',$userid);
$item_num = $mdb2->escape($_GET['page']);
$send_to = $_POST['send_to'];
$send_message = $_POST['send_message'];
$sql = "SELECT * FROM products WHERE product_id = '$item_num' AND status = 1";
$product_details =& $mdb2->query($sql);
while (($row = $product_details->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$product[] = $row;
}
if ($product_details->numRows() < 1){
	$smarty->display('item_no.tpl');
	exit;
}
$smarty->assign('products',$product);
///Pull up information about about grower --///
$grower = $product[0][storeid];
$sql2 = "SELECT * FROM sellers WHERE storename LIKE '$grower'";
$store_details =& $mdb2->query($sql2);
while (($row = $store_details->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$store[] = $row;
}
$smarty->assign('store',$store);
$sql9 = "SELECT * FROM store_data WHERE store_id LIKE '$grower'";
$store_details2 =& $mdb2->query($sql9);
while (($row = $store_details2->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$store_dets[] = $row;
}
$smarty->assign('store_dets',$store_dets);
///Pull up other listings from this grower
$sql3 = "SELECT * FROM products WHERE storeid LIKE '$grower' AND in_stock > 0 AND status = 1";
$other =& $mdb2->query($sql3);
while (($row = $other->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$other_products[] = $row;
}
$smarty->assign('other_products',$other_products);
//////////-------------------------------/////////////
// --Check to see if seller is part of a co-op ----///
/////////------------------------------//////////////
$sql6 = "SELECT * FROM co_op_member WHERE member LIKE '$grower'";
$co_op_check =& $mdb2->query($sql6);
if ($co_op_check->numRows() > 0){$co_op_now = true; 
$smarty->assign('co_op_now',$co_op_now);
while ($row = $co_op_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$co_id = $row['co_op'];
}

$sql8 ="SELECT * FROM co_op WHERE co_id LIKE '$co_id'";
$co_name_final =& $mdb2->query($sql8);
while ($row = $co_name_final->fetchRow(MDB2_FETCHMODE_ASSOC)) {
	$co_op_name[] = $row;
}
$smarty->assign('co_op_name',$co_op_name);
}
///-------------------------------------//////
////-------If asked send message-------//////
////------------------------------------/////
if (isset($send_message)){
	$message = $mdb2->escape($_POST['message']);
	$status = 1;
	$sql11 = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$userid','$send_to','$message','$status')";
	$affected =& $mdb2->exec($sql11);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
	$emailsql = "SELECT email FROM userid WHERE uid = '$send_to'";
	$email_check =& $mdb2->query($emailsql);
	while (($row = $email_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	$send_email = $row['email'];
	}
	$message_sent = true; 
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
	$subject = "You have a message from a GrownSmall customer";
	$mail = new SendGrid\Email();
	$mail->addTo($send_email)->
		   setFrom("noreply@grownsmall.com")->
		   setFromName('GrownSmall')->
		   setSubject($subject)->
		   setText($text)->
		   setHtml($htmlmes);
		   $sendgrid->web->send($mail);
	$message_sent = true;
	$smarty->assign('message_sent',$message_sent);
}
$smarty->display('shop.tpl');
exit;
?>
