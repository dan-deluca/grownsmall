<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
session_start();
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
$userid = $_SESSION["sessionusername"];
$formaction = 'seller.php';
$smarty->assign('formaction',$formaction);
$storename = $_SESSION['storename'];
$banner_image = $storename . '.jpg'; 
$smarty->assign('banner_image',$banner_image);
$success = $_GET['success'];
$smarty->assign('success',$success);
//echo $storename;
//exit;
//Kick out those who are not logged in//
///---Delete if asked
if (isset($_GET['delete'])){
    $udelete = $_GET['delete'];
    $usql = "UPDATE products SET status = 2 WHERE product_id = '$udelete'";
    $affected =& $mdb2->exec($usql);

}
if (isset($_GET['renew'])){
    $renew = $_GET['renew'];
    $rsql = "UPDATE products SET status = 1, dateposted = CURRENT_TIMESTAMP WHERE product_id = $renew";
    $affected =& $mdb2->exec($rsql);    
}
//// ------  Select the items for sale from the database --------////
$sql2 = "SELECT * FROM products WHERE storeid LIKE '$storename' AND status = 1";
//$rows = array();
$res =& $mdb2->query($sql2);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$rows[] = $row;
}
$smarty->assign('rows',$rows);
////---SELECT EXPIRED ITEMS FROM DATABASE
//// ------  Select the items for sale from the database --------////
$sql7 = "SELECT * FROM products WHERE storeid LIKE '$storename' AND status = 0";
//$rows = array();
$res2 =& $mdb2->query($sql7);
while ($row = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$expired[] = $row;
}
$smarty->assign('expired',$expired);
if (isset($_POST['sale_lock'])){
    //exit; 
    $raw_status = $_POST['sale_lock'];
    if ($raw_status == 1){$status = 0;}
    else{$status = 1;}
    $statusup = "UPDATE store_data SET cut_off = '$status' WHERE store_id LIKE '$storename'";
    $affected =& $mdb2->exec($statusup);
}
///----------------------------------------------////////
//---  Select Seller Details From Database ------///////
$sql4 = "SELECT * FROM store_data WHERE store_id LIKE '$storename'";
$res2 =& $mdb2->query($sql4);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
if ($res2->numRows() == 0){
    header("Location: http://www.grownsmall.com/store_data.php");
}
$smarty->assign('dets',$dets);
//Get the store's display name ////
$sql5 = "SELECT displayname FROM sellers WHERE uid LIKE '$userid'";
$display_name_check =& $mdb2->query($sql5);
while ($row = $display_name_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $displayname = $row['displayname'];
}
$smarty->assign('displayname',$displayname);

//////////-------------------------------/////////////
// --Check to see if seller is part of a co-op ----///
/////////------------------------------//////////////
$sql6 = "SELECT * FROM co_op_member WHERE member LIKE '$storename'";
$co_op_check =& $mdb2->query($sql6);
if ($co_op_check->numRows() > 0){$co_op_now = true; 
$smarty->assign('co_op_now',$co_op_now);
while ($row = $co_op_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_id = $row['co_op'];
}

$sql8 ="SELECT * FROM co_op WHERE co_id LIKE '$co_id'";
$co_name_final =& $mdb2->query($sql8);
while ($row = $co_name_final->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_op_name[] = $row;
}
$smarty->assign('co_op_name',$co_op_name);
}
///Find out where the location is///
$sqldan = "SELECT * FROM zip_stores WHERE store_id LIKE '$storename'";
$res =& $mdb2->query($sqldan);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$zip_data[] = $row;
}
$smarty->assign('zip_data',$zip_data);
///End find store zips
/// --If asked uploadbanner and resize---///
if (isset($_POST['banner'])){
    $temp = explode(".", $_FILES["bannerfile"]["name"]);
    $extension = end($temp);
    $tempbanner = $_FILES['bannerfile']['tmp_name'];
    $uploaddir = '/var/www/vhosts/grownsmall.com/httpdocs/product_images/';
    $finalbanner = $uploaddir . $storename . '.' . $extension;
    include('SimpleImage.php'); 
    $image = new SimpleImage();
     $image->load($_FILES['bannerfile']['tmp_name']);
      $image->resizeToWidth(750);
      $image->save($finalbanner);
      $updatesql = "UPDATE store_data SET banner = 1 WHERE store_id = '$storename'";
      $affected =& $mdb2->exec($updatesql);
      $bannerimage = true;
      $smarty->assign('bannerimage',$bannerimage);
}
///----If asked to do so, enter subfunction to place New Items for sale into database ----/////
if (isset($_POST['newitem'])){
//echo "new item";
//exit;
//-- Set variables collected from form to add to database----////
$item_name = $mdb2->escape($_POST['item_name']);
$smarty->assign('item_name',$item_name);
$item_price = $_POST['item_price'];
$smarty->assign('item_price',$item_price);
$item_catagory = $_POST['item_catagory'];
$smarty->assign('item_catagory',$item_catagory);
$in_stock = $_POST['in_stock'];
$smarty->assign('in_stock',$in_stock);
$description = $mdb2->escape($_POST['description']);
$units = $mdb2->escape($_POST['units']);
$smarty->assign('description',$description);
$organic = $_POST['organic'];
$heirloom = $_POST['heirloom'];
$pestiside = $_POST['pestiside'];
$tempimage = $_FILES['userfile']['tmp_name'];
//echo $tempimage; 
//exit;
$temp = explode(".", $_FILES["userfile"]["name"]);
$extension = end($temp);
//echo $tempimage;
//exit;
//--- ---//
//--- Check to see if user left anything important out ---///
if (empty($item_name) OR empty($item_price)){
 if (empty($item_name)){$error[] = "Please provide a name for your item"; }  
 if (empty($item_price)){$error[] = "You must put in a price above $.00";}
}
if (!empty($item_price) AND !is_numeric($item_price)){$error[] = "Please only enter numbers for the item's price";}
if ($item_catagory == 'null'){$error[] = "Please choose a catagoty for your item.";}
if (empty($in_stock)){$error[] = "You must provide how many of the item are in stock.";}
if (isset($error)){
    $smarty->assign('error',$error);
    $smarty->display('seller_style.tpl');
    exit;
}
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
  $image->scale(99);
  $image->save($final_image_path);

$sql = "INSERT INTO products (storeid,item_name,item_price,units,item_catagory,in_stock,description,organic,heirloom,pestiside,filename,thumb_file) VALUES ('$storename','$item_name','$item_price','$units','$item_catagory','$in_stock','$description','$organic','$heirloom','$pestiside','$webdirectory','$webdirectory_thumb')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
//$smarty->display('seller.tpl');
header("Location: http://www.grownsmall.com/seller.php?success=yes");
exit;
    }

else{
/// -- They did not put in a file, use alternate option ------///////
$webload = "product_images/";
$stock_image = $webload . $item_catagory . '.jpg';
$stock_image_thumb = $webload . $item_catagory . '.thumb.jpg';
$sql = "INSERT INTO products (storeid,item_name,item_price,item_catagory,in_stock,description,organic,heirloom,pestiside,filename,thumb_file) VALUES ('$storename','$item_name','$item_price','$item_catagory','$in_stock','$description','$organic','$heirloom','$pestiside','$stock_image','$stock_image_thumb')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
header("Location: http://www.grownsmall.com/seller.php?success=yes");
exit;
}
}
/////----------------display the template ---------------///

$smarty->display('seller_style.tpl');
exit;
?>