<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include('zipcode.php');
// connect to the MySQL database with the zip code table
mysql_connect('localhost', 'gsmall2', 'f?J5tl80');
mysql_select_db('gsmallDB2');
require_once 'config.php';
$formaction = 'index2.php';
$smarty->assign('formaction',$formaction);
$zip_final_1 = $_GET['zip'];
$zip_final_2 = new ZipCode($zip_final_1);
//echo $zip_final;
///--------------------------------/////////////
///--- Find Zip Codes Within 10 Miles ----/////
///--------------------------------/////////////
foreach ($zip_final_2->getZipsInRange(0, 10) as $miles => $zip) {
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
$sql3 = "SELECT * FROM sellers WHERE zipp IN ($ids)";
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
$sql5 = "SELECT * FROM products WHERE storeid IN ('$stores')";
$products =& $mdb2->query($sql5);
while (($row = $products->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$items[] = $row;
}
$smary->assign('items',$items);
?>
