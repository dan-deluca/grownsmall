<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
include('zipcode.php');
// connect to the MySQL database with the zip code table
mysql_connect('localhost', 'gsmall2', 'f?J5tl80');
mysql_select_db('gsmallDB2');
require_once 'config.php';
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$formaction = 'co_op.php';
$smarty->assign('formaction',$formaction);
$username = $_SESSION["sessionusername"];
$storename = $_SESSION['storename'];
///--------------------------------------------------------------------------////
/// First Check if user is the administrator of the Co - Op                  ////
///--------------------------------------------------------------------------////
$sql4 = "SELECT * FROM co_op WHERE co_admin LIKE '$username'";
$admin_check =& $mdb2->query($sql4); 
if ($admin_check->numRows() > 0){
while ($row = $admin_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_op_id = $row['co_id'];
}$_SESSION['co_op_id'] = $co_op_id;
header("Location: http://www.grownsmall.com/co_op_admin.php");
exit;
}
///--------------------------------------------------------------------------////
/// If the user is a member but not and adminstarator redirect them to the Co-Op Page////
///--------------------------------------------------------------------------////
$sql5 = "SELECT co_op FROM co_op_member WHERE member LIKE '$storename'"; 
$co_op_member_check =& $mdb2->query($sql5);
if ($co_op_member_check->numRows() > 0){
while ($row = $co_op_member_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_id = $row['co_op'];
}
$sql6 = "SELECT co_user_name FROM co_op WHERE co_id = '$co_id'";
$address_check =& $mdb2->query($sql6);
while ($row = $address_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_op_address = $row['co_user_name'];
}
header("Location: http://www.grownsmall.com/coop/$co_op_address/");
exit;
}
///--------------------------------------------------------------------------////
/// SEARCH FOR EXISTING CO-OP's THAT ARE NEAR THE SELLER's FARM              ////
///--------------------------------------------------------------------------////

//-- Get The Zip Code of the current Seller --///
$sql = "SELECT zipp FROM sellers WHERE uid LIKE '$username'";
$zip_code_check =& $mdb2->query($sql);
while ($row = $zip_code_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $zip_user = $row['zipp'];
}
$zip_final = new ZipCode($zip_user);
///--------------------------------/////////////
///--- Find Zip Codes Within 10 Miles ----/////
///--------------------------------/////////////
foreach ($zip_final->getZipsInRange(0, 20) as $miles => $zip) {
   $zip_array[] = $zip->getZipC();
}
array_push($zip_array, $zip_user);
$ids = join(',',$zip_array);
//print_r($ids);
//exit;
//Search For Any Co-Op's within 10 Miles Using the Zip Codes Just Found --- 
$sql2 = "SELECT * FROM co_op WHERE co_zip IN ($ids)";
$res2 =& $mdb2->query($sql2);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
$smarty->assign('dets',$dets);
////-----------------------------------------////
/// If Requested add seller to selected CoOP ////
////-----------------------------------------////
if (isset($_POST['co_id'])){
$co_op_id = $_POST['co_id'];
$sql3 = "INSERT INTO co_op_member (member,co_op,status) VALUES ('$storename','$co_op_id','1')";
$affected =& $mdb2->exec($sql3);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
header("Location: http://www.grownsmall.com/seller.php");
exit;
}
////-----------------------------------------////
/// If Requested Create a Co-OP Assign User as Admin ////
////-----------------------------------------////
if (isset($_POST['Submit'])){
$co_user_name = $mdb2->escape($_POST['co_user_name']);
$co_admin = $username;
$co_description = $mdb2->escape($_POST['co_description']);
$co_name = $mdb2->escape($_POST['co_name']);
$co_status = 0; 
$co_delivery_info = $mdb2->escape($_POST['co_delivery_info']);
$co_zip = $_POST['co_zip'];
$sql7 = "INSERT INTO co_op (co_user_name,co_admin,co_description,co_name,co_status,co_delivery_info,co_zip) VALUES ('$co_user_name','$co_admin','$co_description','$co_name','$co_status','$co_delivery_info','$co_zip')";
$sql8 = "SELECT co_user_name FROM co_op WHERE co_user_name LIKE '$co_user_name'";
$co_user_name_check =& $mdb2->query($sql8);
if(preg_match("/^[0-9]{5}$/", $co_zip)) { 
$zipcheck = valid;
} 
else{
$error[] = "Please enter a valid Zip Code";}
if (empty($_POST["co_user_name"])) {$error[] = "Please add a Cooperative user name";}
if (empty($_POST["co_description"])) {$error[] = "Please add description of your cooperative ";}
if (empty($_POST["co_name"])) {$error[] = "Please adda a name for your cooperative";}
if (empty($_POST["co_zip"])) {$error[] = "Please provide a zip code";}
if ($co_user_name_check->numRows() > 0){
$error[] = "That Co-Op user name is allready in use!";
}
else{$all_clear = valid;}
$smarty->assign('error',$error);
if (!isset($error)){$affected =& $mdb2->exec($sql7);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
$coidsql = "SELECT co_id FROM co_op WHERE co_user_name LIKE '$co_user_name'";
$co_idcheck =& $mdb2->query($coidsql);
while (($row = $co_idcheck->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_db_id = $row[co_id];
}
$sql3 = "INSERT INTO co_op_member (member,co_op,status) VALUES ('$storename','$co_db_id','1')";
$affected =& $mdb2->exec($sql3);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
$_SESSION['co_op_id'] = $co_db_id;
//Row added notw redirect to admin page; 
$_SESSION['co_op_sesh'] = $co_user_name;
header("Location: http://www.grownsmall.com/co_op_admin.php?new=true");
exit;}
}
$mdb2->disconnect();
////-----------------------------------------////
/// END CREATE CO -OP                        ////
////-----------------------------------------////
$smarty->display('coop_man.tpl');
?>