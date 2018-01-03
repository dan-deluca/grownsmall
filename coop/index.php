<?php
//test of save
require_once '../config.php';
$formaction = 'index.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
session_start();
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
$co_op_name = $mdb2->escape($_GET['page']);
$sql = "SELECT * FROM co_op WHERE co_user_name LIKE '$co_op_name'";
$co_op_check =& $mdb2->query($sql);
if ($co_op_check->numRows() > 0){
$co_op_test = true; 
$smarty->assign('co_op_test',$co_op_test); 
while ($row = $co_op_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $co_id_array[] = $row;
}
$smarty->assign('co_id_array',$co_id_array);
}
else{
    $smarty->display('co_op_no.tpl');
    exit;
}
////---SEND MESSAGE TO CO-OP ADMIN THEN THROUGH SENDGRID
if (isset($_POST['send_to'])){
    $message = $mdb2->escape($_POST['message']);
    $send_to = $_POST['send_to'];
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
    $subject = "You have a message about your GrownSmall Cooperative";
    $mail = new SendGrid\Email();
    $mail->addTo($send_email)->
           setFrom("noreply@grownsmall.com")->
           setFromName('GrownSmall')->
           setSubject($subject)->
           setText($text)->
           setHtml($htmlmes);
           $sendgrid->web->send($mail);
           $sent = valid;
           $smarty->assign('sent',$sent); 
    $message_sql = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$userid','$send_to','$message','1')";
    $affected =& $mdb2->exec($message_sql);
    if (PEAR::isError($affected)) {
        die($affected->getMessage());
    }

    $smarty->assign('message_sent',$message_sent);
    
}

//// ----------------------------------------------------/////
////----- If this really is a Co - Op  Find Products-----/////
//// ----------------------------------------------------/////
if (isset($co_op_test)){
$co_id = $co_id_array[0]["co_id"];
$sql2 = "SELECT member FROM co_op_member WHERE co_op = '$co_id' AND status = '1'";
$members_check =& $mdb2->query($sql2);
while (($row = $members_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$members[] = $row;
}
$callback = function($value) {
   return $value['member'];
};
$result = array_map($callback, $members);
//print_r($result);

$ids = join("','", $result);
$sql3 = "SELECT * FROM products,store_data WHERE products.storeid = store_data.store_id AND storeid IN ('$ids') AND in_stock > 0 AND status = 1";
$products_sql =& $mdb2->query($sql3);
while ($row = $products_sql->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $products[] = $row;
}
$smarty->assign('products',$products);
//// ----------------------------------------------------/////
////----- Now Find all members of the Coop          -----/////
//// ----------------------------------------------------/////
$sql4 = "SELECT * FROM sellers INNER JOIN store_data ON sellers.storename = store_data.store_id  WHERE sellers.storename IN ('$ids')";
$farmers_sql =& $mdb2->query($sql4);
while ($row = $farmers_sql->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $farmers[] = $row;
}
$smarty->assign('farmers',$farmers);
}
//// ----------------------------------------------------/////
////----------------------------- END--------------------/////
//// ----------------------------------------------------/////
else{
    $smarty->display('coop404.tpl');
exit;
}
$smarty->display('coop_style.tpl');
?>