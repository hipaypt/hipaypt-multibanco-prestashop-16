<?php
    error_reporting(E_ALL & ~E_NOTICE);

    include(dirname(__FILE__).'/../../config/config.inc.php');
    include(dirname(__FILE__).'/../../header.php');
    include(dirname(__FILE__).'/wfxcomprafacil.php');
    
    $md5 = $_GET["payment"];
    $db = Db::getInstance();
    $payment = $db->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'wfxcomprafacil` WHERE `md5` ="'.$md5.'";');
    $payment = $payment[0];
    
    if (!$payment) {
        die(Tools::displayError('Código de pagamento inválido.'));
    }

    if ($payment[status] == 1) {
        die(Tools::displayError('Pagamento ja efectuado.'));
    }

    //Confirmar pagamento CF
    include(dirname(__FILE__).'/class.comprafacil.php');
    $compraFacil = new CompraFacil();
    if(!$compraFacil->verify_payment($payment[reference])){ 
        die(Tools::displayError("A referência ".$payment[reference]." ainda não foi paga"));
    }

    $orderMessage = "date: ".date("Y-m-d H:i:s")."\nref: ".$payment[reference];
    $id_order = $payment['order_id'];
    $order = new Order((int)$id_order);
    if ((int)$order->getCurrentState() == (int)Configuration::get('HIPAYMB_AUTHORIZATION_OS') || (int)$order->getCurrentState() == (int)Configuration::get('HIPAYMB_CAPTURE_OS')) {
        $orderHistory = new OrderHistory();
        $orderHistory->id_order = (int)$order->id;
        $orderHistory->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), (int)$id_order);
        $orderHistory->addWithemail();

    }
    if ($payment[status] != 1) {
        $db = Db::getInstance();
        $db->execute("UPDATE `"._DB_PREFIX_."wfxcomprafacil` SET `status`='1', `payment_date`='".date("Y-m-d H:i:s")."' WHERE `md5` ='".$md5."';");
    }    

    $id_land = Language::getIdByIso($defaultCountry->iso_code);     //Set the English mail template
    $template_name = 'wfxcomprafacil_confirm'; //Specify the template file name
    $title = Mail::l('Payment Confirmed'); //Mail subject with translation
    $from = Configuration::get('PS_SHOP_EMAIL');   //Sender's email
    $fromName = Configuration::get('PS_SHOP_NAME'); //Sender's name
    $mailDir = dirname(__FILE__).'/mails/'; //Directory with message templates
    $templateVars =    array(
        '{order_name}' => $payment[order_id]);
    if ($payment[status] != 1) Mail::Send($id_land, $template_name, $title, $templateVars, $from, $fromName, $from, $fromName, $fileAttachment, NULL, $mailDir);
    exit();