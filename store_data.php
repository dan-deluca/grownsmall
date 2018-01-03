<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$storename = $_SESSION['storename'];
//Check to see if store allready has details
$sql4 = "SELECT store_id FROM store_data WHERE store_id LIKE '$storename'";
$doublenamecheck =& $mdb2->query($sql4);
if ($doublenamecheck->numRows() > 0){
$sql5 = "SELECT * FROM store_data WHERE store_id LIKE '$storename'";
$res =& $mdb2->query($sql5);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$rows[] = $row;
}
$smarty->assign('rows',$rows);
$update = true;
//echo "description exist";
//exit;
}
/////--------//////
$sqldan = "SELECT * FROM zip_stores WHERE store_id LIKE '$storename'";
$res =& $mdb2->query($sqldan);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$zip_data[] = $row;
}
$smarty->assign('zip_data',$zip_data);
$userid = $_SESSION["sessionusername"];
$formaction = 'store_data.php';
$smarty->assign('formaction',$formaction);
if (isset($_POST['zip'])){
    $user_zip = $_POST['zip'];
    $match = preg_match("#[0-9]{5}#", $user_zip); 
    if ($match == 0){$error = "Please enter valid zip code";
    $smarty->assign('error',$error);
    $smarty->display('farm_dets.tpl');
    exit;
    }
    else{
        $insql = "INSERT INTO zip_stores (zip_store,store_id) VALUES ('$user_zip','$storename')";
        $affected =& $mdb2->exec($insql);
        if (PEAR::isError($affected)) {
            die($affected->getMessage());
        }header("Location: http://www.grownsmall.com/seller.php");
    }
}
if (isset($_POST['personal_info'])){
$storename = $_SESSION['storename'];
$farm_description = $mdb2->escape($_POST['farm_description']);
$harvest_date = $mdb2->escape($_POST['harvest_date']);
$personal_info = $mdb2->escape($_POST['personal_info']);
$farm_delivery = $mdb2->escape($_POST['farm_delivery']);
if ($update == true){
$sql = "UPDATE store_data SET farm_description = '$farm_description', harvest_date = '$harvest_date', personal_description = '$personal_info', farm_delivery = '$farm_delivery' WHERE store_id = '$storename'";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
header("Location: http://www.grownsmall.com/seller.php");
exit;
}
else{
$sql = "INSERT INTO store_data (store_id,farm_description,harvest_date,personal_description,farm_delivery) VALUES ('$storename','$farm_description','$harvest_date','$personal_info','$farm_delivery')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());

}header("Location: http://www.grownsmall.com/seller.php");
exit;}
}
$smarty->display('farm_dets.tpl');
exit;

?>