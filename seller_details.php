<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$order_number = $_GET['order'];
$smarty->assign('order_number',$order_number);
$formaction = 'seller_details.php?order='.$order_number;
$smarty->assign('formaction',$formaction);
$order_number = $_GET['order'];
$smarty->assign('order_number',$order_number);
$smarty->assign('userid',$userid);
$storename = $_SESSION['storename'];
$send_message = $_POST['send_message'];
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
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
/// If asked mark all orders as delivered /////
if (isset($_GET['confirm'])){
	require('chedder/Client.php');
	require('chedder/Client/Exception.php');
	require('chedder/Client/AdapterInterface.php');
	require('chedder/Client/CurlAdapter.php');
	require('chedder/Response.php');
	require('chedder/Response/Exception.php');
	require('chedder/Http/AdapterInterface.php');
	require('chedder/Http/NativeAdapter.php');
	$client = new CheddarGetter_Client(
		'https://cheddargetter.com/',
		'email',
		'string',
		'GROWNSMALL'
	);
	$ched_tot = $_SESSION['order_total'];
	$client->addItemQuantity(
		$userid, 
		null, 
		array('itemCode'=>'income', 'quantity'=>$ched_tot)
	);
	
	$update_sql = "UPDATE order_items SET item_stat = 2 WHERE order_number = '$order_number' AND seller_store = '$storename' AND item_stat = 1";
	$affected =& $mdb2->exec($update_sql);
	
header("Location: http://www.grownsmall.com/seller_orders.php");
exit;
}
//////-----------/////////
///If asked cancell an item!---/////
if (isset($_GET['remove'])){
	$remove = $_GET['remove'];
	$rsql = "UPDATE order_items SET item_stat = 0 WHERE order_index = '$remove'";
	$affected =& $mdb2->exec($rsql);
}
/////
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
	$smarty->assign('message_sent',$message_sent);
}
///end--//
///find if this is a closed or open order---///
$tsql = "SELECT * FROM order_items WHERE order_number = '$order_number' AND seller_store = '$storename' AND item_stat = 2"; 
$closed_check =& $mdb2->query($tsql);
$closed = $closed_check->numRows();
if ($closed > 0){
	$closed_smart = true;
	$smarty->assign('closed_smart',$closed_smart);
	
}
//finds information about all itms sold through co-ops 
$sql = "SELECT order_index,item_number, price, quant, item_stat, item_name, item_catagory, in_stock, filename, thumb_file, buyer, first_name, last_name FROM order_items, products, myorder, userid WHERE order_items.order_number = '$order_number' AND order_items.seller_store = '$storename' AND order_items.item_number = products.product_id AND order_items.order_number = myorder.order_number and myorder.buyer= userid.uid";
$coop_products =& $mdb2->query($sql);
while (($row = $coop_products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$coop_pro[] = $row;
}
$smarty->assign('coop_pro',$coop_pro);
////Find total of non cancelled items -------//////////////
$sqla = "SELECT SUM(price*quant) as co_total FROM order_items WHERE order_items.order_number = '$order_number' AND order_items.seller_store = '$storename' AND item_stat != 0";
$co_total =& $mdb2->query($sqla);
while (($row = $co_total->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_tot = $row['co_total'];
}
if (!isset($co_tot)){$co_tot = 0;}
$smarty->assign('co_tot',$co_tot);
$_SESSION['order_total'] = $co_tot;
///--------------------------------/////
//echo $val_total;
$smarty->display('seller_details.tpl');
exit;
?>