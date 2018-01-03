<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
if (!isset($_SESSION["sessionusername"])){
header('index.php');
exit;
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minutes ago
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$closed = $_GET['closed'];
$smarty->assign('closed',$closed);
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
$order_number = $_GET['order'];
$smarty->assign('order_number',$order_number);
$formaction = 'order_details.php?order='.$order_number;
$smarty->assign('formaction',$formaction);
$order_number = $_GET['order'];
$smarty->assign('order_number',$order_number);
$smarty->assign('userid',$userid);
$send_message = $_POST['send_message'];
if (isset($_GET['confirm'])){
	$update_sql = "UPDATE myorder SET order_status = 1 WHERE order_number = '$order_number'";
	$affected =& $mdb2->exec($update_sql);
header("Location: http://www.grownsmall.com/orders.php");
exit;
}
///-------------------------------------//////
////-------If asked send message-------//////
////------------------------------------/////
if (isset($send_message)){
	$message = $mdb2->escape($_POST['message']);
	$send_to = $_POST['send_to'];
	$status = 1;
	$sql11 = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$userid','$send_to','$message','$status')";
	$affected =& $mdb2->exec($sql11);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
	$message_sent = true;
	$emailsql = "SELECT email FROM userid WHERE uid = '$send_to'";
	$email_check =& $mdb2->query($emailsql);
	while (($row = $email_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	$send_email = $row['email'];
	}
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

	$smarty->assign('message_sent',$message_sent);
}
///end--//
//finds information about all itms sold through co-ops 
$sql = "SELECT * FROM order_items, co_op_member, co_op, products WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id AND order_items.item_number = products.product_id";
$coop_products =& $mdb2->query($sql);
while (($row = $coop_products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$coop_pro[] = $row;
}
$smarty->assign('coop_pro',$coop_pro);
////Find total of non cancelled items -------//////////////
$sqla = "SELECT SUM(price*quant) as co_total FROM order_items, co_op_member, co_op, products WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id AND order_items.item_number = products.product_id AND item_stat != 0";
$co_total =& $mdb2->query($sqla);
while (($row = $co_total->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_tot = $row['co_total'];
}
if (!isset($co_tot)){$co_tot = 0;}
$smarty->assign('co_tot',$co_tot);
///--------------------------------/////
$sql2 = "SELECT co_op.co_name, co_op.co_user_name, co_op.co_delivery_info, co_op.co_admin FROM co_op, order_items, co_op_member WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id GROUP BY co_op.co_user_name";
$coop_query =& $mdb2->query($sql2);
while (($row = $coop_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$coop[] = $row;
}
$smarty->assign('coop',$coop);
////---------------------------////
$sql3 = "SELECT * FROM order_items INNER JOIN products ON order_items.item_number = products.product_id LEFT JOIN co_op_member ON order_items.seller_store = co_op_member.member  WHERE order_items.order_number = '$order_number' AND co_op_member.co_op_member_id IS NULL";
$farm_items =& $mdb2->query($sql3);
while (($row = $farm_items->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$farm_sales[] = $row;
}
$smarty->assign('farm_sales',$farm_sales);
////Select total from the farmers side of the /////
$sql3a = "SELECT SUM(price*quant) as co_total FROM order_items INNER JOIN products ON order_items.item_number = products.product_id LEFT JOIN co_op_member ON order_items.seller_store = co_op_member.member  WHERE order_items.order_number = '$order_number' AND co_op_member.co_op_member_id IS NULL AND item_stat != 0";
$farm_total =& $mdb2->query($sql3a);
while (($row = $farm_total->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$farm_tot = $row['co_total'];
}
if (!isset($farm_tot)){$farm_tot = 0;}
//////-----------------------------------------///////
$sql4 = "SELECT sellers.displayname, store_data.farm_delivery, order_items.seller_store, sellers.uid FROM order_items INNER JOIN sellers ON order_items.seller_store = sellers.storename INNER JOIN store_data ON sellers.storename = store_data.store_id LEFT JOIN co_op_member ON order_items.seller_store = co_op_member.member WHERE order_items.order_number = '$order_number' AND co_op_member.co_op_member_id IS NULL GROUP By order_items.seller_store";
$farm_query =& $mdb2->query($sql4);
while (($row = $farm_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$farms[] = $row;
}
$smarty->assign('farms',$farms);
$final_total = $farm_tot + $co_tot;
$smarty->assign('final_total',$final_total);
//echo $val_total;
$smarty->display('order_details.tpl');
exit;
?>