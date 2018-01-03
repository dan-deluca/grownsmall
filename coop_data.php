<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$storename = $_SESSION['storename'];
$username = $_SESSION["sessionusername"];
$co_op_sesh = $_SESSION['co_op_sesh'];
$co_op_id = $_SESSION['co_op_id'];
$formaction = 'coop_data.php';
$smarty->assign('formaction',$formaction);
$sql = "SELECT * FROM co_op WHERE co_id = '$co_op_id'";
$coop_check =& $mdb2->query($sql);
while (($row = $coop_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$rows = $row;
}
$smarty->assign('rows',$rows);
//var_dump($rows);
///--- Update Details When Form is filled---///
if (isset($_POST['submit'])){
    $co_description = $mdb2->escape($_POST['co_description']);
    $co_name = $mdb2->escape($_POST['co_name']);
    $co_delivery_info = $mdb2->escape($_POST['co_delivery_info']);
    if ($_POST['status'] == 1){
        $checked = 1; 
    }
    else {
        $checked = 0; 
    }
$sql = "UPDATE co_op SET co_description = '$co_description', co_name = '$co_name', co_delivery_info = '$co_delivery_info', co_status = '$checked' WHERE co_id = '$co_op_id'";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
header("Location: http://www.grownsmall.com/co_op.php");
exit;
}
$smarty->display('coop_dets.tpl');
exit;

?>