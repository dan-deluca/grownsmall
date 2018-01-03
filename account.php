<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
  // last request was more than 30 minutes ago
  session_unset();     // unset $_SESSION variable for the run-time 
  session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (!isset($_SESSION["sessionusername"])){
header('index.php');
exit;
}
$error = array();
$userid = $_SESSION["sessionusername"];
$formaction = 'account.php';
$smarty->assign('formaction',$formaction);
$productKey = '97a946ee7c261ae8333d271767ab2d4b';
$account_key = substr(md5($userid . '|' . $productKey), 0, 10);
$smarty->assign('account_key',$account_key);
$smarty->assign('userid',$userid);
$new = $_GET['new'];
$smarty->assign('new',$new);
/// Run a query to check if the user allready has a store account
if (!isset($_SESSION['allready_store'])){
$sql4 = "SELECT storename FROM sellers WHERE uid LIKE '$userid'";
$doublenamecheck =& $mdb2->query($sql4);
if ($doublenamecheck->numRows() > 0){
while ($row = $doublenamecheck->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $storename = $row['storename'];
}
////-----------------------------------------------------------/////////
///Seller has seller account in GrownSmall, now check ChedderGetter///
////-----------------------------------------------------------/////////
  require('chedder/Client.php');
    require('chedder/Client/Exception.php');
    require('chedder/Client/AdapterInterface.php');
    require('chedder/Client/CurlAdapter.php');
    require('chedder/Response.php');
    require('chedder/Response/Exception.php');
    require('chedder/Http/AdapterInterface.php');
    require('chedder/Http/NativeAdapter.php');
  $client = new CheddarGetter_Client(
      'https://cheddargetter.com/',
      'email',
      'custom_string',
      'GROWNSMALL'
  );
  //echo $userid;
  
  try {
  $customers = $client->getCustomer($userid);
  $xml = new SimpleXMLElement($customers);
      //echo "<pre>";
      //print_r($xml);
      //echo "</pre>";
  $test = $xml->customer->subscriptions->subscription->canceledDatetime;
  $paypal = $xml->customer->subscriptions->subscription->cancelType;
  $nextbill = $xml->customer->subscriptions->subscription->plans->plan->nextInvoiceBillingDatetime;
  //echo $nextbill;
  if (isset($test)){
      if ($paypal == 'paypal-wait'){$chedsell = false;  }
      elseif (strtotime($nextbill) > time() ){
          $chedsell = true;
      }
      else {$chedsell = false; }
      }
      else{$chedsell = true;}
      }
       catch (Exception $e) {
       $chedsell = false;
   }
   ///-----We have checked ChedderGetter now apply some rules based on results ---////
   if($chedsell == true){
       /// Redirect to Seller's Homepage -- 
$_SESSION['storename'] = $storename;
$allready_store = true;
$_SESSION['allready_store'] = $allready_store;
$smarty->assign('allready_store',$allready_store);
//$nameErr = "You allready have an sellers account";
   }
   elseif($chedsell == false){
       $chederror = true; 
       $smarty->assign('chederror',$chederror);
       //echo "errorTEST";
   }

}

elseif (isset($_POST['submit'])){
$userid = $_SESSION["sessionusername"];
$storename = $mdb2->escape($_POST['storename']);
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
$error[] = "Please enter a valid US Zip Code";
}
//end check zippcode
/// Check Store Name ///
if (empty($_POST["storename"])) {
        $nameErr = "Please provide a storename";
    }
    elseif ($storenamecheck->numRows() > 0){
    $error[] = "That storename is allready in use";
    //echo 'stop';
    //exit;
    }
    elseif (preg_match('/^[a-z\d_]{5,20}$/i', $storename)) {
    $name = valid; 
} else {
    $error[] = "Your storename is an invalid format, please choose a name between 5 and 20 characters using only letters, number and underscores!";
}
///---------Check to see if any errors are set------///
if (!empty($error)){
$smarty->assign('error',$error);
$smarty->display('account.tpl');
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
$sql2 = "INSERT INTO zip_stores (zip_store,store_id) VALUES ('$storename','$zip')";
$affected =& $mdb2->exec($sql2);
if (PEAR::isError($affected)) {
    die($affected->getMessage());
}
$smarty->display('account.tpl');
exit;}
}}
else{$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
//echo "hello dan";
}
$smarty->display('account.tpl');
$mdb2->disconnect();
exit;
?>