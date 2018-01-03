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
  //echo $userid;
$userid = $_SESSION['sessionusername'];
$plan = $_GET['plan'];
$sql = "SELECT * FROM userid WHERE uid = '$userid'";
$user_data =& $mdb2->query($sql);
while (($row = $user_data->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$userarray[] = $row;
}
//print_r($userarray);
$fname = $userarray[0]["first_name"];
$lname = $userarray[0]["last_name"];
$demail = $userarray[0]["email"];
//echo $fname;
$client = new CheddarGetter_Client(
	'https://cheddargetter.com/',
	'email',
	'customstring',
	'GROWNSMALL'
);

	try {
		$response = $client->deleteCustomer('TEST_CUSTOMER000');
		echo "\n\tDeleted Milton Waddams\n";
	} catch (Exception $e) {}
if ($plan != 'OFFLINE_UNLIMITED'){
$data = array(
	'code'      => $userid,
	'firstName' => $fname,
	'lastName'  => $lname,
	'email'     => $demail,
	'subscription' => array(
		'planCode'      => $plan,
		'ccFirstName'   => $fname,
		'ccLastName'    => $lname,
		'method'			=> 'paypal',
		'returnUrl'      => 'http://www.grownsmall.com/login.php',
		'cancelUrl'  => 'http://www.grownsmall.com/',
	)
);}
else {
	$data = array(
		'code'      => $userid,
		'firstName' => $fname,
		'lastName'  => $lname,
		'email'     => $demail,
		'subscription' => array(
			'planCode'      => $plan,
		)
	);
	$customer = $client->newCustomer($data);
	header('Location:http://www.grownsmall.com/account.php');
exit;
}
$customer = $client->newCustomer($data);
$xml = new SimpleXMLElement($customer);
//echo "<pre>";
////PLAN CODES /////
//FIVE_PERCENT
//OFFLINE_UNLIMITED
//UNLIMITED_YEARLY	
	
//print_r($xml);
//echo "</pre>";
//echo"<hr>";
$redirectUrl = $xml->customer->subscriptions->subscription->redirectUrl;
 header('Location:'.$redirectUrl);
?>