<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$formaction = 'co_op_distribute.php';
$smarty->assign('formaction',$formaction);
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
	'custom string',
	'GROWNSMALL'
);
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
$co_op_num = $_SESSION['co_op_id'];
$select = "SELECT co_user_name FROM co_op WHERE co_id = '$co_op_num'";
$orders =& $mdb2->query($select);
while (($row = $orders->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_op_id = $row[co_user_name];
}
function guid(){
	if (function_exists('com_create_guid')){
		return com_create_guid();
	}else{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
		return $uuid;
	}
}$d_id = guid();
if (isset($_POST['order_in'])){
	$order_index = $_POST['order_in'];
	$index_string = implode(", ", $order_index);
	echo $index_string;
	$sum_sql = "SELECT seller_store, uid, SUM(price*quant) as item_total FROM order_items JOIN sellers ON order_items.seller_store = sellers.storename WHERE order_index IN($index_string) GROUP BY seller_store";
	$members_sql =& $mdb2->query($sum_sql);
	while (($row = $members_sql->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	$member_total[] = $row;
	}
	$smarty->assign('member_total',$member_total);
	$insert_array = array(); 
	foreach ($member_total as $row) {
		$insert_array[] = '("'.$d_id.'", "'.$row['seller_store'].'", '.$row['item_total'].')';
	}
	foreach ($member_total as $row){
		$client->addItemQuantity(
			$row['uid'], 
			null, 
			array('itemCode'=>'income', 'quantity'=>$row['item_total'])
		);
	}
	$insert_code = "INSERT INTO distribution_amounts (dist_un, member, amount) VALUES ".implode(',', $insert_array);
	$affected =& $mdb2->exec($insert_code);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
$sql5 = "INSERT INTO distributions (dis_un, co_op) VALUES ('$d_id', '$co_op_id')";
$affected =& $mdb2->exec($sql5);
	$update_sql = "UPDATE order_items SET item_stat = 2 WHERE order_index IN ($index_string)";
	$affected =& $mdb2->exec($update_sql);
	if (PEAR::isError($affected)) {
		die($affected->getMessage());
	}
$smarty->display('totals.tpl');
exit;
}

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
if (isset($_GET['close'])){
///THIS WILL FIND the total owed to each seller from all open orders 
$sql = "SELECT seller_store, uid, SUM(price*quant) AS item_total FROM order_items JOIN myorder ON order_items.order_number = myorder.order_number JOIN co_op_member ON co_op_member.member = order_items.seller_store JOIN co_op ON co_op.co_id = co_op_member.co_op JOIN sellers ON order_items.seller_store = sellers.storename WHERE co_op.co_user_name = '$co_op_id' AND order_items.item_stat = 1 GROUP BY seller_store";
$members_check =& $mdb2->query($sql);
while (($row = $members_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$totals[] = $row;
}
$smarty->assign('member_total',$totals);
$totals_array = array();
foreach ($totals as $row ) {
	$totals_array[] = '("'.$d_id.'","'.$row['seller_store'].'", '.$row['item_total'].')';
}
$insert_code2 = "INSERT INTO distribution_amounts (dist_un, member, amount) VALUES ".implode(',', $totals_array);
$insert_code3 = "INSERT INTO distributions (dis_un, co_op) VALUES ('$d_id', '$co_op_id')";
$affected =& $mdb2->exec($insert_code2);
$affected =& $mdb2->exec($insert_code3);
foreach ($totals as $row){
	$client->addItemQuantity(
		$row['uid'], 
		null, 
		array('itemCode'=>'income', 'quantity'=>$row['item_total'])
	);
}
///  This query returns the order_index of all open items sold by co_op
$sql2 = "SELECT order_items.order_index FROM order_items JOIN co_op_member ON co_op_member.member = order_items.seller_store JOIN co_op ON co_op.co_id = co_op_member.co_op WHERE co_op.co_user_name = '$co_op_id' AND order_items.item_stat = 1";
$index_check =& $mdb2->query($sql2);
while (($row = $index_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$index[] = $row;
}
$callback = function($value) {
   return $value['order_index'];
};
$result = array_map($callback, $index);

$implode_index = implode(',', $result);
$update_sql = "UPDATE order_items SET item_stat = 2 WHERE order_index IN ($implode_index)";
$affected =& $mdb2->exec($update_sql);
if (PEAR::isError($affected)) {
	die($affected->getMessage());
}
$smarty->display('totals.tpl');
exit;
}
//echo $implode_index;
$mdb2->disconnect();
$smarty->display('coop_re.tpl');

exit;
?>