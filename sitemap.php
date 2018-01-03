<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$sql1 = "SELECT storename FROM sellers";
$sellers_check =& $mdb2->query($sql1);
while (($row = $sellers_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$sellers[] = $row;
}
$smarty->assign('sellers',$sellers);
$sql2 = "SELECT product_id FROM products WHERE status = 1";
$products_check =& $mdb2->query($sql2);
while (($row = $products_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$products[] = $row;
}
$smarty->assign('products',$products);
$sql3 = "SELECT co_user_name FROM co_op";
$products_check =& $mdb2->query($sql3);
while (($row = $products_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_op[] = $row;
}
$smarty->assign('co_op',$co_op);
$smarty->display('sitemap.tpl');
$mdb2->disconnect();
exit;
?>