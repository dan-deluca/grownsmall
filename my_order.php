<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'config.php';
$formaction = 'order_summery.php';
$smarty->assign('formaction',$formaction);
$order_number = $_GET['order'];
$sql = "SELECT order_number FROM order_items WHERE seller_store = 'evilmonkey' GROUP BY order_number";
$sql2 = "SELECT order_number FROM order_items WHERE seller_store = 'evilmonkey'";
$sql3 = "SELECT SUM(price*quant), order_number FROM `order_items` WHERE seller_store = 'evilmonkey' GROUP BY order_number";
//finds information about all itms sold through co-ops 
$smarty->display('order_over.tpl');
exit;
?>