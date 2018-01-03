<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$formaction = 'orders.php';
$smarty->assign('formaction',$formaction);
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minutes ago
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION["sessionusername"])){
header('index.php');
exit;
}
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$sql = "SELECT myorder.order_number, SUM(price*quant) as order_total, order_time FROM myorder INNER JOIN order_items ON myorder.order_number = order_items.order_number WHERE myorder.buyer = '$userid' AND myorder.order_status = 0 AND item_stat != 0 AND item_stat != 3 GROUP BY myorder.order_number ORDER BY order_time DESC";
$order_check =& $mdb2->query($sql);
while (($row = $order_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$orders[] = $row;
}
$smarty->assign('orders',$orders);
//echo $val_total;
$mdb2->disconnect();
$smarty->display('order.tpl');
exit;
?>