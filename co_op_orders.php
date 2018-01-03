<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$formaction = 'co_op_orders.php';
$co_op_num = $_SESSION['co_op_id'];
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
$select = "SELECT co_user_name FROM co_op WHERE co_id = '$co_op_num'";
$orders =& $mdb2->query($select);
while (($row = $orders->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_op_id = $row[co_user_name];
}
$smarty->assign('formaction',$formaction);
if (isset($_GET['remove'])){
	$remove = $_GET['remove'];
	$rsql = "UPDATE order_items SET item_stat = 0 WHERE order_index = '$remove'";
	$affected =& $mdb2->exec($rsql);
}
///THIS WILL FIND ALL OPEN ORDER's WITH ITEM DETAILS GIVEN THE CO-OP 
$sql = "SELECT order_items.order_number, order_index, price, quant, item_name, item_price, seller_store, price*quant AS item_total FROM order_items JOIN myorder ON order_items.order_number = myorder.order_number JOIN products ON order_items.item_number = products.product_id JOIN co_op_member ON co_op_member.member = order_items.seller_store JOIN co_op ON co_op.co_id = co_op_member.co_op WHERE co_op.co_user_name = '$co_op_id' AND order_items.item_stat = 1";

$order_pro =& $mdb2->query($sql);
while (($row = $order_pro->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$order_products[] = $row;
}
$smarty->assign('order_products',$order_products);
/// THIS Will SELECT THE ODER NUMBER BUYER AND SELLER AND ORDER PRICE GROUPED BY ORDER /// 

$sql2 = "SELECT SUM(price*quant) AS order_total, seller_store, order_items.order_number, buyer, first_name, last_name FROM order_items JOIN myorder ON order_items.order_number = myorder.order_number JOIN userid ON userid.uid = myorder.buyer JOIN co_op_member ON co_op_member.member = order_items.seller_store JOIN co_op ON co_op.co_id = co_op_member.co_op WHERE co_op.co_user_name = '$co_op_id' AND order_items.item_stat = 1 GROUP BY order_items.order_number";
$orders =& $mdb2->query($sql2);
while (($row = $orders->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$order[] = $row;
}
$smarty->assign('order',$order);
///---THIS QUERY WILL FIND THE TOTAL OF ALL OPEN CO-OP ORDERSA
$sql3 = "SELECT SUM(price*quant) AS coop_total FROM order_items JOIN myorder ON order_items.order_number = myorder.order_number JOIN products ON order_items.item_number = products.product_id JOIN co_op_member ON co_op_member.member = order_items.seller_store JOIN co_op ON co_op.co_id = co_op_member.co_op WHERE co_op.co_user_name = '$co_op_id' AND order_items.item_stat = 1";
$total_sql =& $mdb2->query($sql3);
while (($row = $total_sql->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$total[] = $row;
}
$smarty->assign('total',$total);
//echo "hellp";
$mdb2->disconnect();
$smarty->display('coop_orders.tpl');
exit;
?>