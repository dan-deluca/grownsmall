<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'config.php';
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
  require('chedder/Client.php');
  require('chedder/Client/Exception.php');
  require('chedder/Client/AdapterInterface.php');
  require('chedder/Client/CurlAdapter.php');
  require('chedder/Response.php');
  require('chedder/Response/Exception.php');
  require('chedder/Http/AdapterInterface.php');
  require('chedder/Http/NativeAdapter.php');
$userid = $_SESSION['sessionusername'];
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
	if ($paypal = 'paypal-wait'){$chedsell = false;}
	elseif (strtotime($nextbill) > time() ){
		$chedsell = true;
	}
	else {$chedsell = false;}
	}
	else{$chedsell = true;}
	}
	 catch (Exception $e) {
	 $chedsell = false;
 }

?>