<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
require 'vendor/autoload.php';
$sendgrid = new SendGrid('username', 'password');
if (!isset($_SESSION["sessionusername"])){
header("Location: http://www.grownsmall.com/");
exit;
}
$formaction = 'co_op_admin.php';
$smarty->assign('formaction',$formaction);
$username = $_SESSION["sessionusername"];
$storename = $_SESSION['storename'];
$smarty->assign('storename',$storename);
$co_op_sesh = $_SESSION['co_op_sesh'];
$co_op_id = $_SESSION['co_op_id'];
/// ----------------------------------------- ///
/// Update Co-op Member status                ///  
/// ----------------------------------------- ///
if (isset($_GET['approve'])){
    $approvevalue = $_GET['approve'];
    $app_sql = "UPDATE co_op_member SET status = '1' WHERE co_op_member_id = '$approvevalue'";
    $affected =& $mdb2->exec($app_sql);
    if (PEAR::isError($affected)) {
        die($affected->getMessage());
    }
}
if (isset($_GET['suspend'])){
    $suspendvalue = $_GET['suspend'];
    $sus_sql = "UPDATE co_op_member SET status = '0' WHERE co_op_member_id = '$suspendvalue'";
    $affected =& $mdb2->exec($sus_sql);
    if (PEAR::isError($affected)) {
        die($affected->getMessage());
    }
}
if (isset($_POST['sale_lock'])){
    //exit; 
    $raw_status = $_POST['sale_lock'];
    if ($raw_status == 1){$status = 0;}
    else{$status = 1;}
    $statusup = "UPDATE co_op SET co_status = '$status' WHERE co_id LIKE '$co_op_id'";
    $affected =& $mdb2->exec($statusup);
    
}
/// ----------------------------------------- ///
/// IF NEW CO_OP DO Fancy SQL WORK to FIND ID ///  
/// ----------------------------------------- ///
if (isset($new)){
$sql = "SELECT * FROM co_op WHERE co_id LIKE '$co_op_sesh'";
$res2 =& $mdb2->query($sql);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
$smarty->assign('dets',$dets);}
else{
$sql = "SELECT * FROM co_op WHERE co_id LIKE '$co_op_id'";
$res2 =& $mdb2->query($sql);
while ($det = $res2->fetchRow(MDB2_FETCHMODE_ASSOC)) {
$dets[] = $det;
}
$smarty->assign('dets',$dets);

}
/// ----------------------------------------- ///
/// END CO_OP DO Fance SQL WORK to FIND ID ///  
/// ----------------------------------------- ///
/// ----------------------------------------- ///
/// PULL UP ALL MEMBERS OF THE CO-OP and      ///  
/// ----------------------------------------- ///
$sql2 = "SELECT * FROM co_op_member WHERE co_op = '$co_op_id'";
$members_check =& $mdb2->query($sql2);
while (($row = $members_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$members[] = $row;

}
$callback = function($value) {
   return $value['member'];
};
$result = array_map($callback, $members);

$ids = join("','", $result);
$sql3 = "SELECT * FROM sellers INNER JOIN co_op_member ON sellers.storename = co_op_member.member INNER JOIN store_data ON sellers.storename = store_data.store_id WHERE co_op_member.member IN ('$ids')";
$co_op_members =& $mdb2->query($sql3);
while (($row = $co_op_members->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_members[] = $row;
}
$smarty->assign('co_members',$co_members);
/// ----------------------------------------- ///
/// PULL UP ALL MEMBERS OF THE CO-OP END!!      ///  
/// ----------------------------------------- ///
/// ----------------------------------------- ///
/// Find the number of members who are pending  ///  
/// ----------------------------------------- ///
$sql6 = "SELECT * FROM sellers INNER JOIN co_op_member ON sellers.storename = co_op_member.member WHERE co_op_member.member IN ('$ids') AND co_op_member.status = '0'";
$co_op_pending =& $mdb2->query($sql6);
if ($co_op_pending->numRows() == 0){$no_pending = true;
    $smarty->assign('no_pending',$no_pending);}
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!////
/// ----------------------------------------- ///
/// PULL UP ITEMS FOR SALE                    ///  
/// ----------------------------------------- ///
$sql4 = "SELECT * FROM products WHERE storeid IN ('$ids') AND status = 1";
$products_sql =& $mdb2->query($sql4);
while ($row = $products_sql->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $products[] = $row;
}
$smarty->assign('products',$products);
/// ----------------------------------------- ///
/// WE have finnished pulling items for sale  ///  
/// ----------------------------------------- ///
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!////
/// ----------------------------------------- ///
/// If nessary UPDATE all members to "LOCK"   ///  
/// ----------------------------------------- ///
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!////
if (isset($_POST['sale_lock'])){
    //exit; 
    $raw_status = $_POST['sale_lock'];
    if ($raw_status == 1){$status = 0;}
    else{$status = 1;}
    $statusup = "UPDATE store_data SET cut_off = '$status' WHERE store_id IN ('$ids')";
    $affected =& $mdb2->exec($statusup);
}
if (isset($_POST['recipiant'])){
    $message = $mdb2->escape($_POST['new_message']);
    $send_to = $_POST['recipiant'];
        if ($send_to == 'all'){
            //place messages into our database///
        $all_sql = "SELECT uid FROM sellers WHERE storename IN ('$ids')";
                $message_check =& $mdb2->query($all_sql);
                while (($row = $message_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
               $loop_sql = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$username','$row[uid]','$message','1')";
        $affected =& $mdb2->exec($loop_sql);
        $email_raw[] = $row;
        }
        ////We need to get a list of emails to send to sendgrid////
            ///FIRST WE NEED TO GET THE USERID's OF THE STORES IN CO_OP///
              $callback = function($value) {
                 return $value['uid'];
              };
              $eresult = array_map($callback, $email_raw);
              $eids = join("','", $eresult);
              ///$eids is the usernames of the co_op now make a query to find emails
              $esql = "SELECT email FROM userid WHERE uid IN ('$eids')";
              $email_check =& $mdb2->query($esql);
              while (($row = $email_check->fetchRow(MDB2_FETCHMODE_ASSOC))) {
                  $realmail[] = $row;
              }
              $callback2 = function($value) {
                 return $value['email'];
              };
              $eresult2 = array_map($callback2, $realmail);
              //$sendmail = join("','", $eresult2);
              ///$sendmail is the list of email address of cooperative members///
        ////////END FIND EMAILS
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
                $subject = "You have a message from your GrownSmall cooperative";
                $mail = new SendGrid\Email();
                $mail->setTos($eresult2)->
                       setFrom("noreply@grownsmall.com")->
                       setFromName('GrownSmall')->
                       setSubject($subject)->
                       setText($text)->
                       setHtml($htmlmes);
                       $sendgrid->web->send($mail);
                       $sent = valid;
                       $smarty->assign('sent',$sent); 
                
                $message_sent = true;
                $smarty->assign('message_sent',$message_sent);
    }
    else{
    $message_sql = "INSERT INTO messages (sent_from,sent_to,message,status) VALUES ('$username','$send_to','$message','1')";
    $affected =& $mdb2->exec($message_sql);
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
    $subject = "You have a message from your GrownSmall cooperative";
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

    $smarty->assign('message_sent',$message_sent);
    }
}

/// ----------------------------------------- ///
/// Edit Various things that need editing     ///  
/// ----------------------------------------- ///
////Co Op Member status codes
/// 1 = FULL MEMBER 
/// 0 = WAITING APROVAL 
/// 2 = SUSPENDEDd
$mdb2->disconnect();
$smarty->display('coop_admin_style.tpl');
?>