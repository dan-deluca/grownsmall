<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
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
$formaction = 'seller_orders.php';
$smarty->assign('formaction',$formaction);
$smarty->assign('userid',$userid);
$storename = $_SESSION['storename'];
$send_message = $_POST['send_message'];
//finds information about all itms sold through co-ops 
$sql = "SELECT SUM(price*quant) as order_total, order_items.order_number, price, quant, item_stat, buyer, order_status, first_name, last_name, order_time FROM order_items, myorder, userid WHERE order_items.seller_store = '$storename' AND order_items.order_number = myorder.order_number AND myorder.buyer = userid.uid AND order_items.item_stat != 1 AND order_items.item_stat != 0 GROUP BY myorder.order_number";
$coop_products =& $mdb2->query($sql);
while (($row = $coop_products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$order_pro[] = $row;
}
$smarty->assign('order_pro',$order_pro);
$sql2 = "SELECT SUM(price*quant) as member_total FROM order_items WHERE order_items.seller_store = '$storename' AND item_stat = 2";
$shop_query =& $mdb2->query($sql2);
while (($row = $shop_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$seller_total = $row['member_total'];
}
$smarty->assign('seller_total',$seller_total);
$smarty->display('seller_order2.tpl');
exit;
?>