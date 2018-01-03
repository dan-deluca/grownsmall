<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/login.php?checkout=true");
exit;
}
$username = $_SESSION["sessionusername"];
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
}
if ( $_POST['shoppingCart'] ) {
$shoppingCart = array();
foreach ( $_POST['shoppingCart'] as $item ) {
	if (is_array($item)) {
		$shoppingCart[] = $item;
	}
}
$num_sql = "SHOW TABLE STATUS LIKE 'myorder'";
$num_check =& $mdb2->query($num_sql);
while (($row = $num_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$order_number = $row['auto_increment'];
}
$sql_items = array(); 
$sql1 = "INSERT INTO myorder (buyer, order_status) VALUES ('$username', 0)";
foreach ($shoppingCart as $row ) {
	$sql_items[] = '('.$order_number.', '.$row['id'].', "'.$row['sell'].'", '.$row['price'].', '.$row['qty'].')';
}
$insert_code = "INSERT INTO order_items (order_number, item_number, seller_store, price, quant) VALUES ".implode(',', $sql_items);
$affected =& $mdb2->exec($sql1);
$affected2 =& $mdb2->exec($insert_code);
$_SESSION['order'] = $order_number;
//echo $sql1;
//echo $insert_code;
// echo '<pre>'; print_r( $sql_items ); echo '</pre>';
 exit;
 }
$mdb2->disconnect();
$smarty->display('cart.tpl');
exit;
?>