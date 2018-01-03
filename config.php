<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//Call Databse Abstration Layer
require_once 'MDB2.php';
session_start();
//Start Templateing Engine
require_once 'Smarty.class.php';
require_once 'password.php';
$smarty = new Smarty();
$smarty->setTemplateDir('/var/www/vhosts/grownsmall.com/smarty/templates');
$smarty->setCompileDir('/var/www/vhosts/grownsmall.com/smarty/templates_c');
$smarty->setCacheDir('/var/www/vhosts/grownsmall.com/smarty/cache');
$smarty->setConfigDir('/var/www/vhosts/grownsmall.com/smarty/configs');
// Open a connection to the database
$user = 'dbuser';
$pass = 'db_password';
$host = 'localhost';
$db_name = 'gsmallDB2';
$dsn = "mysql://$user:$pass@$host/$db_name";
$mdb2 =& MDB2::connect($dsn);
if (PEAR::isError($mdb2)) {
    die($mdb2->getMessage());
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
if (isset($_SESSION["sessionusername"])){
    $userid = $_SESSION["sessionusername"];
    $sql4 = "SELECT * FROM co_op WHERE co_admin LIKE '$userid'";
    $admin_check =& $mdb2->query($sql4); 
    if ($admin_check->numRows() > 0){
    while ($row = $admin_check->fetchRow(MDB2_FETCHMODE_ASSOC)) {
        $co_op_id = $row['co_id'];
    }
    $_SESSION['co_op_id'] = $co_op_id;
    $co_admin = true;
    $smarty->assign('co_admin',$co_admin);
    }
    $logged_in = true; 
    $smarty->assign('logged_in',$logged_in);
    $namesql = "SELECT * FROM userid WHERE uid LIKE '$userid'";
    $user_info =& $mdb2->query($namesql);
    while (($row = $user_info->fetchRow(MDB2_FETCHMODE_ASSOC))) {
    $user_stuff[] = $row;
    }
    $smarty->assign('user_stuff',$user_stuff);
    $mail_sql = "SELECT * FROM messages WHERE sent_to LIKE '$userid' AND status = '1'";
    $mail_check =& $mdb2->query($mail_sql); 
    if ($mail_check->numRows() > 0){
        $new_mail = true;
        $smarty->assign('new_mail',$new_mail);
    }
}
if (isset($_POST['s'])){
    $s = $_POST['s'];
    header("Location: http://www.grownsmall.com/coop/$s");
}
$global_username = $_POST['global_username'];
$global_password = $_POST['global_password'];
if (isset($_POST['global_submit'])){
$sql = "SELECT * from userid where uid LIKE '$global_username'"; 
$res =& $mdb2->query($sql);
while ($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
    $hashed = $row['password'];
    $username = $row['uid'];
}
if (password_verify($global_password, $hashed)) {
   session_start(); // start session, duh.
   $_SESSION["sessionusername"] = $global_username;
   $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
   $login_sucess = true;
   $smarty->assign('login_sucess',$login_sucess);
} else {
   header("Location: http://www.grownsmall.com/login.php?error=true");
exit;
}
}
?>