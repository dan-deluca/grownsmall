<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
// put full path to Smarty.class.php
require('Smarty.class.php');
$smarty = new Smarty();

$smarty->setTemplateDir('/var/www/vhosts/grownsmall.com/smarty/templates');
$smarty->setCompileDir('/var/www/vhosts/grownsmall.com/smarty/templates_c');
$smarty->setCacheDir('/var/www/vhosts/grownsmall.com/smarty/cache');
$smarty->setConfigDir('/var/www/vhosts/grownsmall.com/smarty/configs');

$smarty->assign('name', 'Ned');
$smarty->display('index.tpl');

?>