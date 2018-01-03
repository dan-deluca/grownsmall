<?php
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
   $smarty->assign('username',$global_username);
   header("Location: http://www.grownsmall.com/account.php");
   exit;
} else {
   header("Location: http://www.grownsmall.com/login.php?error=true");
exit;
}
}
?>