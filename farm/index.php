<?php
require_once '../config.php';
$formaction = 'index.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
include('zipcode.php');
// connect to the MySQL database with the zip code table
mysql_connect('localhost', 'gsmall2', 'f?J5tl80');
mysql_select_db('gsmallDB2');
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
$userid = $_SESSION["sessionusername"];
$smarty->assign('userid',$userid);
$send_to = $_POST['send_to'];
$send_message = $_POST['send_message'];
$storename = $mdb2->escape($_GET['page']);
$banner_file = $storename . '.jpg';
$smarty->assign('banner_file',$banner_file);
$sql2 = "SELECT * FROM products,store_data WHERE products.storeid = store_data.store_id AND storeid LIKE '$storename' AND in_stock > 0 AND status = 1";
//$rows = array();
$res =& $mdb2->query($sql2);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$rows[] = $row;
}
$smarty->assign('rows',$rows);
///----------------------------------------------////////
//---  Select Seller Details From Database ------///////
$sql4 = "SELECT * FROM store_data WHERE store_id LIKE '$storename'";
$res2 =& $mdb2->query($sql4);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
$smarty->assign('dets',$dets);
if ($res2->numRows() < 1){
    $smarty->display('farm_no.tpl');
    exit;
}
//Get the store's display name ////
$sql5 = "SELECT displayname FROM sellers WHERE storename LIKE '$storename'";
$display_name_check =& $mdb2->query($sql5);
while ($row = $display_name_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $displayname = $row['displayname'];
}
$smarty->assign('displayname',$displayname);
$sql55 = "SELECT uid FROM sellers WHERE storename LIKE '$storename'";
$u_name_check =& $mdb2->query($sql55);
while ($row = $u_name_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $send_to = $row['uid'];
}
///find the zip code --///
$zipql = "SELECT zipp FROM sellers WHERE storename LIKE '$storename'";
$zip_check =& $mdb2->query($zipql);
while ($row = $zip_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $zip = $row['zipp'];
}
$zipp = new ZipCode($zip);
$city = $zipp->getCity();
$state = $zipp->getStatePrefix();
$smarty->assign('city',$city);
$smarty->assign('state',$state);
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
$smarty->assign('co_id',$co_id);
$sql8 ="SELECT * FROM co_op WHERE co_id LIKE '$co_id'";
$co_name_final =& $mdb2->query($sql8);
while ($row = $co_name_final->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_op_name[] = $row;
}
$smarty->assign('co_op_name',$co_op_name);
}
///-------------------------------------//////
////-------If asked send message-------//////
////------------------------------------/////
if (isset($send_message)){
    $message = $mdb2->escape($_POST['message']);
    $status = 1;
    $sql11 = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$userid','$send_to','$message','$status')";
    $affected =& $mdb2->exec($sql11);
    if (PEAR::isError($affected)) {
        die($affected->getMessage());
    }
    $emailsql = "SELECT email FROM userid WHERE uid = '$send_to'";
    $email_check =& $mdb2->query($emailsql);
    while (($row = $email_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
    $send_email = $row['email'];
    }
    $message_sent = true; 
    $text = "Hello, 
    You have received a message from a user on GrownSmall.  Please log into your Inbox to view and reply to the message.  If you have any trouble with abuse of the messaging system, please immediately contact the GrownSmall team.  
    
    Thank you for using GrownSmall!
    
    GrownSmall.com
    732-977-4326
    143 Walnford Rd 
    Allentown, NJ 08501";
    $htmlmes = "<p>Hello, <br>
    
    You have received a message from a user on GrownSmall.  Please log into <a href='http://www.gronwsmall.com/login.php'>your account</a> to view and reply to the message.  If you have any trouble with abuse of the messaging system, please immediately contact the GrownSmall team.  </p>
    
    <p>Thank you for using GrownSmall!</p>
    <p>
    GrownSmall.com<br>
    732-977-4326<br>
    143 Walnford Rd <br>
    Allentown, NJ 08501</p>";
    $subject = "You have a message from a GrownSmall customer";
    $mail = new SendGrid\Email();
    $mail->addTo($send_email)->
           setFrom("noreply@grownsmall.com")->
           setFromName('GrownSmall')->
           setSubject($subject)->
           setText($text)->
           setHtml($htmlmes);
           $sendgrid->web->send($mail);
    $message_sent = true;
    $smarty->assign('message_sent',$message_sent);
}

/////----------------display the template ---------------///

$smarty->display('farm.tpl');
exit;
?>
