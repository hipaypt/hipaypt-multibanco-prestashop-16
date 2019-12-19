<?php
error_reporting(E_ALL & ~E_NOTICE);

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/wfxcomprafacil.php');
 
if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
 
$wfxcomprafacil = new wfxcomprafacil();
echo $wfxcomprafacil->execPayment($cart);
 
include_once(dirname(__FILE__).'/../../footer.php');
?>