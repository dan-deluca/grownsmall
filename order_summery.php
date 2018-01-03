<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
$formaction = 'order_summery.php';
$smarty->assign('formaction',$formaction);
$order_number = $_SESSION['order'];
if (isset($_GET['remove'])){
	$deleteitem = $_GET['remove'];
	$delsql = "DELETE FROM order_items WHERE order_index = '$deleteitem'";
	$affected =& $mdb2->exec($delsql);
}
if (isset($_POST['update_quant'])){
	$update_quant = $_POST['update_quant'];
	$index = $_POST['index'];
	$update_sql = "UPDATE order_items SET quant = '$update_quant' WHERE order_index = '$index'";
	$affected =& $mdb2->exec($update_sql);
}
if (isset($_GET['confirm'])){
//RUN A DB QUERY TO FIND THE NUMBER OF ITEMS PLUS SET UP LOOK FOR COOPS and FARMS
$co_op_quant = "SELECT order_items.item_number, order_items.quant FROM order_items, co_op_member, co_op, products WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id AND order_items.item_number = products.product_id";
$coop_up_query =& $mdb2->query($co_op_quant);
while (($row = $coop_up_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$sqlup = "UPDATE products SET in_stock = in_stock -". $row['quant'] ." WHERE product_id = ".$row['item_number'];
//echo $sqlup;
$affected =& $mdb2->exec($sqlup);
}
///Now run one to find the orders that are not ordered by a coop. 
$farm_quant_sql = "SELECT order_items.item_number, order_items.quant  FROM order_items INNER JOIN products ON order_items.item_number = products.product_id LEFT JOIN co_op_member ON order_items.seller_store = co_op_member.member  WHERE order_items.order_number = '$order_number' AND co_op_member.co_op_member_id IS NULL";
$farm_up_query =& $mdb2->query($farm_quant_sql);
while (($row = $farm_up_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$sqlup = "UPDATE products SET in_stock = in_stock -". $row['quant'] ." WHERE product_id = ".$row['item_number'];
//echo $sqlup;
$affected =& $mdb2->exec($sqlup);
}
/////OK WE Subtracted them from the quatn total now lets set the order as made//////
	$update_sql = "UPDATE order_items SET item_stat = 1 WHERE order_number = '$order_number'";
	$affected =& $mdb2->exec($update_sql);
	$smarty->assign('order_number',$order_number);
	if(isset($_SESSION['order'])){
	unset($_SESSION['order']);}
	$findusersql = "SELECT email FROM userid, order_items, sellers WHERE order_items.order_number = '$order_number' AND order_items.seller_store = sellers.storename AND sellers.uid = userid.uid GROUP BY email";
	$email_raw =& $mdb2->query($findusersql);
	while (($row = $email_raw->fetchRow(MDB2_FETCHMODE_ASSOC))) {
	$emails_array[] = $row;
	}
$callback2 = function($value) {
   return $value['email'];
};
$eresult2 = array_map($callback2, $emails_array);
				$text = "Congratulations, 
                You have recieved an order from GrownSmall.  Please log in to view this order.  If you can not fufill this order please contact the customer ASAP.  Do not forget to mark the order as delivered when the sale has been made!    
                
                Thank you for using GrownSmall!
                
                GrownSmall.com
                732-977-4326
                143 Walnford Rd 
                Allentown, NJ 08501";
                $htmlmes = "<p>Congratulations, <br>
                
                You have recieved an order from GrownSmall.  Please log in to view this order.  If you can not fufill this order please contact the customer ASAP.  Do not forget to mark the order as delivered when the sale has been made! </p>
                
                <p>Thank you for using GrownSmall!</p>
                <p>
                GrownSmall.com<br>
                732-977-4326<br>
                143 Walnford Rd <br>
                Allentown, NJ 08501</p>";
                $subject = "You made a sale on GrownSmall.com!";
                $mail = new SendGrid\Email();
                $mail->setTos($eresult2)->
                	   setFrom("noreply@grownsmall.com")->
                	   setFromName('GrownSmall')->
                	   setSubject($subject)->
                	   setText($text)->
                	   setHtml($htmlmes);
                	   $sendgrid->web->send($mail);
                	 
       
	$smarty->display('thank_u.tpl');
	exit;
}
//finds information about all itms sold through co-ops 
$sql = "SELECT * FROM order_items, co_op_member, co_op, products WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id AND order_items.item_number = products.product_id";
$coop_products =& $mdb2->query($sql);
while (($row = $coop_products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$coop_pro[] = $row;
}
$smarty->assign('coop_pro',$coop_pro);
///--------------------------------/////
$sql2 = "SELECT co_op.co_name, co_op.co_user_name, co_op.co_delivery_info FROM co_op, order_items, co_op_member WHERE order_items.order_number = '$order_number' AND order_items.seller_store = co_op_member.member AND co_op_member.co_op = co_op.co_id GROUP BY co_op.co_user_name";
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
$sql4 = "SELECT sellers.displayname, store_data.farm_delivery, order_items.seller_store FROM order_items INNER JOIN sellers ON order_items.seller_store = sellers.storename INNER JOIN store_data ON sellers.storename = store_data.store_id LEFT JOIN co_op_member ON order_items.seller_store = co_op_member.member WHERE order_items.order_number = '$order_number' AND co_op_member.co_op_member_id IS NULL GROUP BY order_items.seller_store";
$farm_query =& $mdb2->query($sql4);
while (($row = $farm_query->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$farms[] = $row;
}
$smarty->assign('farms',$farms);
$val_total = 0;
foreach ($coop_pro as $value ) {
	$val_total += ($value['item_price']*$value['quant']);

}
unset($value);
$val_total2 = 0;
foreach ($farm_sales as $value2) {
	$val_total2 += ($value2['item_price']*$value2['quant']);
} 
$final_total = $val_total + $val_total2;
$smarty->assign('final_total',$final_total);
//echo $val_total;
$smarty->display('order_over.tpl');
exit;
?>