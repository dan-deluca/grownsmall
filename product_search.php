<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
include('zipcode.php');
// connect to the MySQL database with the zip code table
mysql_connect('localhost', 'gsmall2', 'f?J5tl80');
mysql_select_db('gsmallDB2');
if (isset($_POST['zip'])){
	$zip_final_1 = $_POST['zip'];
}
elseif (isset($_GET['zip'])){
	$zip_final_1 = $_GET['zip'];
	
}
else {
	/// Redirect to homepage we need a zip for this to work!
}
$smarty->assign('zip_final_1',$zip_final_1);
///SET SOME SEARCH VARIABLES ///
if ($_POST['distance'] == 10 OR !isset($_POST['distance'])){
	$filter_distance = 10; 
}
else{ 
	$filter_distance = $_POST['distance']; 
	}
	if (!isset($_POST['organic']) OR $_POST['organic'] == 0){
		$filter_organic = "%";
	}
	else{
		$filter_organic = $_POST['organic'];
	}
if (!isset($_POST['heirloom']) OR $_POST['heirloom'] == 0){
	$filter_heirloom = "%";
}
else{
	$filter_heirloom = $_POST['heirloom'];
}
if (!isset($_POST['catagory']) OR $_POST['catagory'] == 0){
	$filter_catagory = '%';
}
else {
	$filter_catagory = $_POST['catagory'];
}
if (!isset($POST['pesticide']) OR $POST['pesticide'] == 0){
	$filter_pesticide = '%';
}
else{
	$fitler_pesticide = $POST['pesticide'];
}
///Assign variables to themplate ///
$smarty->assign('filter_organic',$filter_organic); 
$smarty->assign('filter_heirloom',$filter_heirloom); 
$smarty->assign('filter_catagory',$filter_catagory); 
$smarty->assign('filter_distance',$filter_distance); 

if (isset($zip_final_1)){$zip_final_2 = new ZipCode($zip_final_1);
///--------------------------------/////////////
///--- Find Zip Codes Within 10 Miles ----/////
///--------------------------------/////////////
foreach ($zip_final_2->getZipsInRange(0, $filter_distance) as $miles => $zip) {
   $zip_array[] = $zip->getZipC();
}
///need to add home zip to array!///
array_push($zip_array, $zip_final_2);
$ids = join(',',$zip_array);
///SELECT stores based on the ID's --- ///
$sql4 = "SELECT storename FROM sellers INNER JOIN zip_stores ON zip_stores.store_id = sellers.storename WHERE zip_stores.zip_store IN ($ids)";
$product_check =& $mdb2->query($sql4);
while (($row = $product_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$storenames[] = $row;
}
$callback = function($value) {
   return $value['storename'];
};
$result = array_map($callback, $storenames);
$stores = join("','", $result);
////$stores are the farms of all the sellers ---////

$searchql = "SELECT * FROM products WHERE organic LIKE '$filter_organic' AND item_catagory LIKE '$filter_catagory' AND pestiside LIKE '$filter_pesticide' AND heirloom LIKE '$filter_heirloom' AND status = 1 AND storeid IN ('$stores')";
$members_check =& $mdb2->query($searchql);
while (($row = $members_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$rows[] = $row;
}
$smarty->assign('rows',$rows);}
//echo $searchql;
$mdb2->disconnect();
$smarty->display('search.tpl');
exit;
?>
