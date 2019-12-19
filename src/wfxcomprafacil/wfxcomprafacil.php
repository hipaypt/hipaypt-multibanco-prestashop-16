<?php
error_reporting(E_ALL & ~E_NOTICE);

class wfxcomprafacil extends PaymentModule  //this declares the class and specifies it will extend the standard payment module
{
 
    private $_html = '';
    private $_postErrors = array();
 
    public  $cf_url;
    public  $cf_user;
    public  $cf_pass;
    
    function __construct()
    {
        $this->name = 'wfxcomprafacil';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.2';
		$this->author = 'Mindshaker';
        
        $config = Configuration::getMultiple(array('CF_WEBSERVICEURL', 'CF_USERNAME', 'CF_PASSWORD'));
        if (isset($config['CF_WEBSERVICEURL']))
            $this->cf_url = $config['CF_WEBSERVICEURL'];
        if (isset($config['CF_USERNAME']))
            $this->cf_user = $config['CF_USERNAME'];
        if (isset($config['CF_PASSWORD']))
            $this->cf_pass = $config['CF_PASSWORD'];
 
        parent::__construct(); // The parent construct is required for translations
 
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Hipay Multibanco');
        $this->description = $this->l('Pagamento com Ref Multibanco, pagas em ATM ou Homebanking.');
   		$this->confirmUninstall = 'Deseja desinstalar o m&oacute;dulo de Pagamento por Multibanco?';

        if (!isset($this->cf_url) OR !isset($this->cf_user) OR !isset($this->cf_pass))
            $this->warning = $this->l('Web Service Url, Username and password must be configured in order to use this module correctly.');
 
    }
    
    public function install()
    {
        if (!parent::install()
        OR !$this->createWFXPaymentcardtbl() //calls function to create payment card table
        OR !$this->registerHook('payment')
        OR !$this->registerHook('displayAdminOrder') 
        OR !$this->registerHook('displayOrderDetail') 
        OR !$this->registerHook('paymentReturn'))
        return false;
        return true;
    }
    
    public function uninstall()
    {
        if (!Configuration::deleteByName('CF_WEBSERVICEURL')
                OR !Configuration::deleteByName('CF_USERNAME')
                OR !Configuration::deleteByName('CF_PASSWORD')
                OR !parent::uninstall())
            return false;
        return true;
    }
    
    function createWFXPaymentcardtbl()
    {
        $db = Db::getInstance(); 
        $query = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."wfxcomprafacil` ("
            ."`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,"
            ."`user_id` INT(11) NOT NULL ,"
            ."`order_id` INT(11) NOT NULL ,"
            ."`reference` VARCHAR(100) DEFAULT NULL,"
            ."`entity` VARCHAR(100) DEFAULT NULL,"
            ."`value` FLOAT DEFAULT NULL,"
            ."`products` VARCHAR(10000) DEFAULT NULL,"
            ."`reference_date` DATETIME DEFAULT NULL,"
            ."`payment_date` DATETIME DEFAULT NULL,"
            ."`md5` VARCHAR(100) DEFAULT NULL,"
            ."`status` SMALLINT(6) DEFAULT '0'"
            .") ENGINE = MYISAM ";
        $db->execute($query);

		if (!Configuration::get('HIPAYMB_AUTHORIZATION_OS'))
		{
			$os = new OrderState();
			$os->name = array();
			foreach (Language::getLanguages(false) as $language)
			$os->name[(int)$language['id_lang']] = 'Aguarda pagamento por Multibanco';
			$os->color = '#FF69B4';
			$os->hidden = false;
			$os->send_email = false;
			$os->delivery = false;
			$os->logable = false;
			$os->invoice = false;
			if ($os->add())
			{
				Configuration::updateValue('HIPAYMB_AUTHORIZATION_OS', $os->id);
				copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$os->id.'.gif');
			}
		}
		if (!Configuration::get('HIPAYMB_CAPTURE_OS'))
		{
			$os = new OrderState();
			$os->name = array();
			foreach (Language::getLanguages(false) as $language)
			$os->name[(int)$language['id_lang']] = 'Confirmado pagamento por Multibanco';
			$os->color = '#4169E1';
			$os->hidden = false;
			$os->send_email = true;
			$os->delivery = false;
			$os->logable = false;
			$os->invoice = false;
			if ($os->add())
			{
				Configuration::updateValue('HIPAYMB_CAPTURE_OS', $os->id);
				copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.(int)$os->id.'.gif');
			}
		}



        return true;
    }
    
    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            if (!Tools::getValue('cf_url'))
                $this->_postErrors[] = $this->l('Web Service Url is required.');
            elseif (!Tools::getValue('cf_user'))
                $this->_postErrors[] = $this->l('Username is required.');
            elseif (!Tools::getValue('cf_pass'))
                $this->_postErrors[] = $this->l('Password is required.');
        }
    }
    
    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            Configuration::updateValue('CF_WEBSERVICEURL', Tools::getValue('cf_url'));
            Configuration::updateValue('CF_USERNAME', Tools::getValue('cf_user'));
            Configuration::updateValue('CF_PASSWORD', Tools::getValue('cf_pass'));
        }
        $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
    }
    
    private function _displayWFXComprafacil()
    {
        $this->_html .= '<img src="../modules/wfxcomprafacil/cards.png" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by ATM.').'</b><br /><br />
        '.$this->l('If the client chooses this payment mode, the order will change its status into a \'Waiting for payment\' status.').'<br />
        '.$this->l('Therefore, after the client pays by ATM, you will receive a email and the status will change to "Payment remotely accepeted".').'<br /><br /><br />';
    }

    private function _displayForm()
    {
        $this->_html .=
        '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />'.$this->l('Details').'</legend>
                <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
                    <tr><td colspan="2">'.$this->l('Please specify the comprafacil account details').'.<br /><br /></td></tr>
                    <tr><td width="130" style="height: 35px;">'.$this->l('Web Service Url').'</td><td><input type="text" name="cf_url" value="'.htmlentities(Tools::getValue('cf_url', $this->cf_url), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td width="130" style="height: 35px;">'.$this->l('Username').'</td><td><input type="text" name="cf_user" value="'.htmlentities(Tools::getValue('cf_user', $this->cf_user), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td width="130" style="height: 35px;">'.$this->l('Password').'</td><td><input type="text" name="cf_pass" value="'.htmlentities(Tools::getValue('cf_pass', $this->cf_pass), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td colspan="2" align="center"><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td></tr>
                </table>
            </fieldset>
        </form>';
    }
    
    public function getContent()
    {
        $this->_html = '<h2>'.$this->displayName.'</h2>';

        if (Tools::isSubmit('btnSubmit'))
        {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">'. $err .'</div>';
        }
        else
            $this->_html .= '<br />';

        $this->_displayWFXComprafacil();
        $this->_displayForm();

        return $this->_html;
    }
    
    public function execPayment($cart)
    {
        if (!$this->active)
            return ;
     
        global $cookie, $smarty;
        
        $smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
     
        return $this->display(__FILE__, 'payment_execution.tpl');
    }
    
    function hookPayment($params)
    {
        global $smarty;
     
        $cart_total = $params['cart']->getOrderTotal(true, Cart::BOTH);
        if ($cart_total > 2500) return false;

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"
        ));
     
        return $this->display(__FILE__, 'payment.tpl');
    }    
    
    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return ;

        global $smarty;
        $comprafacil=$_GET["comprafacil"];
        $db = Db::getInstance();
        $result = $db->ExecuteS('SELECT entity, reference,value FROM `'._DB_PREFIX_.'wfxcomprafacil` WHERE `id` ="'.$comprafacil.'";');
        $state = $params['objOrder']->getCurrentState();
        if ($state == Configuration::get('HIPAYMB_AUTHORIZATION_OS')) {
            $smarty->assign(array(
                'value' => number_format($result[0]['value'],2),
                'entity' => $result[0]['entity'],
                'reference' => $result[0]['reference'],
                'status' => 'ok',
                'this_path' => $this->_path,
                'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'

            ));




			//envia email cliente com os dados de pagamento
			$cliente = new Customer($params['objOrder']->id_customer);
			$templateVars = array(
				'{order_name}' => $params['objOrder']->reference,
				'{firstname}' => $cliente->firstname,
				'{lastname}' => $cliente->lastname,
				'{multibanco_path}' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/multibanco.jpg',
				'{entity}' => $result[0]['entity'],
				'{reference}' => $result[0]['reference'],
				'{value}' => number_format($result[0]['value'],2)
			);
						
		    $id_land = (int)$params['objOrder']->id_lang;
		    $template_name = 'wfxcomprafacil_data'; //Specify the template file name
		    $title = $this->l('Dados para Pagamento Multibanco'); //Mail subject with translation
		    $from = Configuration::get('PS_SHOP_EMAIL');   //Sender's email
		    $fromName = Configuration::get('PS_SHOP_NAME'); //Sender's name
		    $mailDir = dirname(__FILE__).'/mails/'; //Directory with message templates  
		    $send = Mail::Send($id_land, $template_name, $title, $templateVars, $cliente->email, $cliente->firstname.' '.$cliente->lastname, $from, $fromName, $fileAttachment, NULL, $mailDir);

		
		} 
		else {
            $smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }


    function hookdisplayAdminOrder($params)
        {

            if (!$this->active)
                return ;

            global $smarty;
            $db = Db::getInstance();
            $order_id    = $params['id_order'];
            $result = $db->ExecuteS('SELECT entity, reference,value FROM `'._DB_PREFIX_.'wfxcomprafacil` WHERE `order_id` ="'.$order_id.'";');
             if (!$result) return "";         
            $smarty->assign(array(
                'value' => number_format($result[0]['value'],2)  . " Euros",
                'entity' => $result[0]['entity'],
                'reference' => $result[0]['reference'],
                'status' => 'ok',
                'this_path' => $this->_path,
                'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'

            ));

        return $this->display(__FILE__, 'payment_admin.tpl');       

     }



    function hookdisplayOrderDetail($params)
    {
        if (!$this->active)
            return;
        

        global $smarty;
        $order_id = $params['order']->id;
        $order = new Order($order_id);

        $db = Db::getInstance();
        $result = $db->ExecuteS('SELECT entity, reference,value FROM `'._DB_PREFIX_.'wfxcomprafacil` WHERE `order_id` ="'.$order_id.'";');
        if (!$result) return "";         
        $smarty->assign(array(
            'value' => number_format($result[0]['value'],2)  . " Euros",
            'entity' => $result[0]['entity'],
            'reference' => $result[0]['reference'],
            'status' => 'ok',
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'

        ));

        return $this->display(__FILE__, 'payment_order.tpl');       


    }


    function readPaymentcarddetails($id_order)
    {
        $db = Db::getInstance();
        $result = $db->ExecuteS('
        SELECT * FROM `'._DB_PREFIX_.'order_paymentcard`
        WHERE `id_order` ="'.intval($id_order).'";');
        return $result[0];
    }
    
}