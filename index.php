<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
include('zipcode.php');
// connect to the MySQL database with the zip code table
mysql_connect('localhost', 'gsmall2', 'f?J5tl80');
mysql_select_db('gsmallDB2');
require_once 'config.php';
$formaction = 'index.php';
$userid = $_SESSION["sessionusername"];
$smarty->assign('formaction',$formaction);
$zip_final_1 = $_POST['zip'];
include('login_include.php');
$smarty->assign('zip_final_1', $zip_final_1);
$new_email = $_POST['subscribe_email'];
///----if someone is logged in then show in header! ---////
if (isset($_SESSION["sessionusername"])){
    $logged_in = true; 
    $smarty->assign('logged_in',$logged_in);
    $namesql = "SELECT * FROM userid WHERE uid LIKE '$userid'";
    $user_info =& $mdb2->query($namesql);
    while (($row = $user_info->fetchRow(MDB2_FETCHMODE_ASSOC))) {
    $user_stuff[] = $row;
    }
    $smarty->assign('user_stuff',$user_stuff);
}
///add person to email list///
if (isset($_POST['letter_sub'])){
$nsql = "INSERT INTO newletter (sub_email) VALUES ('$new_email')";
$affected =& $mdb2->exec($nsql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
$new_customer = valid; 
$smarty->assign('new_customer',$new_customer);
}
if (isset($zip_final_1)){
$zip_final_2 = new ZipCode($zip_final_1);
//echo $zip_final;
///--------------------------------/////////////
///--- Find Zip Codes Within 10 Miles ----/////
///--------------------------------/////////////
foreach ($zip_final_2->getZipsInRange(0, 30) as $miles => $zip) {
   $zip_array[] = $zip->getZipC();
}
///need to add home zip to array!///
array_push($zip_array, $zip_final_2);
$ids = join(',',$zip_array);
//Search For Any Co-Op's within 10 Miles Using the Zip Codes Just Found --- 
$sql2 = "SELECT * FROM co_op WHERE co_zip IN ($ids)";
$res2 =& $mdb2->query($sql2);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
$smarty->assign('dets',$dets);
//search for any farms within 10 miles 
$sql3 = "SELECT * FROM sellers INNER JOIN zip_stores ON zip_stores.store_id = sellers.storename INNER JOIN store_data ON sellers.storename = store_data.store_id WHERE zip_stores.zip_store IN ($ids)";
$res3 =& $mdb2->query($sql3);
while ($farm = $res3->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$farms[] = $farm;
}
$smarty->assign('farms',$farms);
///Get the storenames of all the farms in distance
$sql4 = "SELECT storename FROM sellers WHERE zipp IN ($ids)";
$product_check =& $mdb2->query($sql4);
while (($row = $product_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$storenames[] = $row;
}
$callback = function($value) {
   return $value['storename'];
};
$result = array_map($callback, $storenames);
$stores = join("','", $result);
//select all products///
$sql5 = "SELECT * FROM products,store_data WHERE products.storeid = store_data.store_id AND storeid IN ('$stores') AND in_stock > 0 AND status = 1";
$products =& $mdb2->query($sql5);
while (($row = $products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$items[] = $row;
}
$smarty->assign('items',$items);
$smarty->display('homeshop.tpl');
exit;
}
//Find some random products--/// 
$sql9 = "SELECT * FROM products WHERE in_stock > 0 AND status = 1 ORDER BY RAND() LIMIT 6";
$random_product =& $mdb2->query($sql9);
while ($rand = $random_product->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$random[] = $rand;
}
$smarty->assign('random',$random);
$mdb2->disconnect();
$smarty->display('home2_new.tpl');
?>
