<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'config.php';
$co_op_num = $_SESSION['co_op_id'];
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
$allready_store = $_SESSION['allready_store'];
$smarty->assign('allready_store',$allready_store);
$select = "SELECT co_user_name FROM co_op WHERE co_id = '$co_op_num'";
$orders =& $mdb2->query($select);
while (($row = $orders->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$co_op_id = $row[co_user_name];
}
$formaction = 'co_op_orders.php';
$smarty->assign('formaction',$formaction);
///  --- THIS WILL FIND THE CLOSED DISTRIBUTIONS AND THEIR TOTALS --///
$sql1 = "SELECT SUM(amount) AS order_total, id, member, dis_un, time_stamp FROM distributions JOIN distribution_amounts ON dis_un = dist_un WHERE co_op = '$co_op_id' GROUP BY dis_un";
$dis_sql =& $mdb2->query($sql1);
while (($row = $dis_sql->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$dris[] = $row;
}
$smarty->assign('dris',$dris);
/// THIS Will SELECT THE ODER NUMBER BUYER AND SELLER AND ORDER PRICE GROUPED BY ORDER /// 

$sql2 = "SELECT * FROM distributions JOIN distribution_amounts ON dis_un = dist_un JOIN sellers ON member = storename WHERE co_op = '$co_op_id'";
$orders =& $mdb2->query($sql2);
while (($row = $orders->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$order[] = $row;
}
$smarty->assign('order',$order);
///---THIS QUERY WILL FIND THE TOTAL OF ALL OPEN CO-OP ORDERSA
$sql3 = "SELECT SUM(amount) AS coop_total FROM distributions JOIN distribution_amounts ON dis_un = dist_un WHERE co_op = '$co_op_id'";
$total_sql =& $mdb2->query($sql3);
while (($row = $total_sql->fetchRow(MDB2_FETCHMODE_ASSOC))) {
$total = $row[coop_total];
}
$smarty->assign('total',$total);
//echo "hellp";
$mdb2->disconnect();
$smarty->display('past_dis.tpl');
exit;
?>