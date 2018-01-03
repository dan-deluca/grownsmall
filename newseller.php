<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
session_start();
$userid = $_SESSION["sessionusername"];
$formaction = 'newseller.php';
$smarty->assign('formaction',$formaction);
/// Run a query to check if the user allready has a store account
$sql4 = "SELECT storename FROM sellers WHERE uid LIKE '$userid'";
$doublenamecheck =& $mdb2->query($sql4);
if ($doublenamecheck->numRows() > 0){
while ($row = $doublenamecheck->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $storename = $row['storename'];
}
/// Redirect to Seller's Homepage -- 
$_SESSION['storename'] = $storename;
header("Location: seller.php");
exit;
//$nameErr = "You allready have an sellers account";
}
if (!isset($_SESSION["sessionusername"])){
header('index.php');
exit;
}
elseif (isset($_POST['submit'])){
$userid = $_SESSION["sessionusername"];
$storename = $_POST['storename'];
$displayname = $mdb2->escape($_POST['displayname']);
$zip = $_POST['zip'];
//Run a query to see if a storename allready exists ///
$sql3 = "SELECT * FROM sellers WHERE storename LIKE '$storename'";
$storenamecheck =& $mdb2->query($sql3);

//// -----------/////
//check zipcode
if(preg_match("/^[0-9]{5}$/", $zip)) { 
$zipcheck = valid;
} 
else{
$zippErr = "Please enter a valid US Zip Code";
}
//end check zippcode
/// Check Store Name ///
if (empty($_POST["storename"])) {
        $nameErr = "Please provide a storename";
    }
    elseif ($storenamecheck->numRows() > 0){
    $nameErr = "That storename is allready in use";
    //echo 'stop';
    //exit;
    }
    elseif (preg_match('/^[a-z\d_]{5,20}$/i', $storename)) {
    $name = valid; 
} else {
    $nameErr = "Your storename is an invalid format, please choose a name between 5 and 20 characters using only letters, number and underscores!";
}
///---------Check to see if any errors are set------///
if (isset($nameErr) OR isset($zippErr)){
$smarty->assign('nameErr',$nameErr);
$smarty->assign('zippErr',$ZippErr);
$error = valid; 
$smarty->assign('error',$error);
$smarty->display('newseller.tpl');
$mdb2->disconnect();
exit;
}
/// ----- no Error so now we can record the seller in the database  ------------- /////
else{
$sql = "INSERT INTO sellers (uid,storename,displayname,zipp) VALUES ('$userid','$storename','$displayname','$zip')";
$affected =& $mdb2->exec($sql);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
echo "seller added";
exit;}
}$smarty->display('newseller.tpl');
$mdb2->disconnect();
exit;
?>