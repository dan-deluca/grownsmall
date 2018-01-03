<?php
//test of save
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$formaction = 'edit_item.php';
$smarty->assign('formaction',$formaction);
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
	// last request was more than 30 minutes ago
	session_unset();     // unset $_SESSION variable for the run-time 
	session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
$userid = $_SESSION["sessionusername"];
$smarty->assign('userid',$userid);
$item_num = $mdb2->escape($_GET['item']);
$smarty->assign('item_num',$item_num);
$send_to = $_POST['send_to'];
$send_message = $_POST['send_message'];
////---- IF ASKED UPDATE DETAILS----/////
if (isset($_POST['updateitem'])){
	$price = $mdb2->escape($_POST['price']);
	$item_name = $mdb2->escape($_POST['item_name']);
	$in_stock = $mdb2->escape($_POST['in_stock']);
	$description = $mdb2->escape($_POST['description']);
	$units = $mdb2->escape($_POST['units']);
	$item_form = $_POST['item_form'];
	$updatesql = "UPDATE products SET item_price = '$price', units = '$units', item_name = '$item_name', in_stock = '$in_stock', description = '$description', dateposted = CURRENT_TIMESTAMP WHERE product_id = '$item_form'";
	$affected =& $mdb2->exec($updatesql);
	//$smarty->display('seller.tpl');
	header("Location: http://www.grownsmall.com/seller.php?success=yes");
	exit;
	
	
}
///END UPDATE DETAILS ///////////
//// IF ASKED upload new picture --//// 
if (isset($_POST['newitem'])){
	$item_form = $_POST['item_form'];
	$tempimage = $_FILES['userfile']['tmp_name'];
	//echo $tempimage; 
	//exit;
	$temp = explode(".", $_FILES["userfile"]["name"]);
	$extension = end($temp);
	///Check to see if image is really an image
	if(exif_imagetype($tempimage) != FALSE)
		{
	
		   ///--create a filename and upload a file to the server ----///
	
	// The complete path/filename 
	$uploaddir = '/var/www/vhosts/grownsmall.com/httpdocs/product_images/';
	$final_image_path = $uploaddir . time() . $_SERVER['REMOTE_ADDR'] . '.' . $extension;
	$final_image_path_thumb = $uploaddir . time() . $_SERVER['REMOTE_ADDR'] . 'thumb.' . $extension;
	$webload = "product_images/";
	$webdirectory = $webload . time() . $_SERVER['REMOTE_ADDR'] . '.' . $extension; 
	$webdirectory_thumb = $webload . time() . $_SERVER['REMOTE_ADDR'] . 'thumb.' . $extension; 
	include('SimpleImage.php'); 
	$image = new SimpleImage();
	 $image->load($_FILES['userfile']['tmp_name']);
	  $image->resizeToHeight(250);
	  $image->save($final_image_path_thumb);
	///move the damn thing ///
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $final_image_path)) {
		$move_file = TRUE;
	} else {
		$move_file = FALSE;
		}
	$sql = "UPDATE products SET filename = '$webdirectory', thumb_file = '$webdirectory_thumb'  WHERE product_id = '$item_form'";
	$affected =& $mdb2->exec($sql);
		//$smarty->display('seller.tpl');
	header("Location: http://www.grownsmall.com/seller.php?success=yes");
	exit;
		}
	
	else{
	/// -- They did not put in a file, use alternate option ------///////
	$image_fail = true; 
	$smarty->assign('image_fail',$image_fail);
	}	
}


///end new picture upload
$sql = "SELECT * FROM products WHERE product_id = '$item_num'";
$product_details =& $mdb2->query($sql);
while (($row = $product_details->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$product[] = $row;
}
$smarty->assign('products',$product);
///Pull up information about about grower --///
$grower = $product[0][storeid];
$sql2 = "SELECT * FROM sellers WHERE storename LIKE '$grower'";
$store_details =& $mdb2->query($sql2);
while (($row = $store_details->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$store[] = $row;
}
$smarty->assign('store',$store);
$sql9 = "SELECT * FROM store_data WHERE store_id LIKE '$grower'";
$store_details2 =& $mdb2->query($sql9);
while (($row = $store_details2->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$store_dets[] = $row;
}
$smarty->assign('store_dets',$store_dets);

$smarty->display('shop_edit.tpl');
exit;
?>
