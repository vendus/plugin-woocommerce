<?php

class VP
{
    protected static $_instance = null;
    
    private $_status = false;

    public function __construct()
    {
        $this->run();
    }

    public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function run()
    {
        $this->initConstant();
        $this->initRequire();
        
        $this->_checkStatus();

        VP_Menu::run();

        if(!$this->isActive()) {
            $this->_configPage();
            return;
        }
        
        VP_Orders::run();
        VP_Clients::run();

        $this->initPages();
    }

    public function initConstant()
    {
        $isDev = VP_DEV;

        if($isDev){
            if (!defined('WP_DEBUG')) {
                define('WP_DEBUG', $isDev);
            }
            if (!defined('WP_DEBUG_LOG')) {
                define('WP_DEBUG_LOG', $isDev);
            }
            if (!defined('WP_DEBUG_DISPLAY')) {
                define('WP_DEBUG_DISPLAY', $isDev);
            }
            if (!defined('SCRIPT_DEBUG')) {
                define('SCRIPT_DEBUG', $isDev);
            }

            define('WP_DEBUG', $isDev);
            define('WP_DEBUG_LOG', $isDev); // wp-content/debug.log
            define('WP_DEBUG_DISPLAY', $isDev);
            @ini_set('display_errors', $isDev);
            define('SCRIPT_DEBUG', $isDev);
            //define('SAVEQUERIES', $isDev);

        }

        if($isDev) {
            define('VENDUS_URL', 'http://vendus-pt/hooks/woocommerce/');
        } else {
            define('VENDUS_URL', 'https://www.vendus.pt/hooks/woocommerce/');
        }

        define('VP_URL', plugins_url('/', dirname(__FILE__)));
        
        //define('VP_PATH', plugin_dir_path(__FILE__));
        define('VP_VIEW_PATH', VP_PATH . 'views/');
        define('VP_IMG_PATH', VP_PATH . 'views/');
        define('VP_CSS_PATH', VP_PATH . 'views/');
        define('VP_JS_PATH', VP_PATH . 'views/');

    }

    public function initRequire()
    {
        require_once VP_PATH . 'includes/class-vp-utils.php';
        require_once VP_PATH . 'includes/class-vp-menu.php';
        require_once VP_PATH . 'includes/class-vp-orders.php';
        require_once VP_PATH . 'includes/class-vp-api.php';
        require_once VP_PATH . 'includes/class-vp-clients.php';
    }
    
    public function initPages()
    {
        $this->_configPage();

        $this->_viewPdfHook();
        $this->_invoiceHook();
    }


    private function _configPage()
    {
        if(!function_exists('vp_action_config')) {
            function vp_action_config() {

                if(isset($_GET['action']) && !empty($_GET['action'])) {
                    $id = isset($_GET['id']) ? $_GET['id'] : '';
                    do_action($_GET['action'], $id);
                    exit;
                }

                if(isset($_POST) && !empty($_POST)) {
                    update_option('vp_config_api_key', '');
                    update_option('vp_form_config_register', '');
                    update_option('vp_form_config_exemption', '');
                    update_option('vp_form_config_exemption_law', '');
                    
                    if(isset($_POST['vp_config_api_key'])) {
                        update_option('vp_config_api_key', $_POST['vp_config_api_key']);
                    }

                    if(isset($_POST['vp_form_config_register'])) {
                        update_option('vp_form_config_register', $_POST['vp_form_config_register']);
                    }

                    if(isset($_POST['vp_form_config_exemption'])){
                        update_option('vp_form_config_exemption', $_POST['vp_form_config_exemption']);
                    }
                    if(isset($_POST['vp_form_config_exemption_law'])){
                        update_option('vp_form_config_exemption_law', $_POST['vp_form_config_exemption_law']);
                    }

                    if(isset($_POST['vp_form_config_invoice_type'])) {
                        update_option('vp_form_config_invoice_type', $_POST['vp_form_config_invoice_type']);
                    }

                    VP_Utils::notify('Dados atualizados com suceso!', 'success');
                }
        
                $params = array();
                $apiKey = get_option('vp_config_api_key');
                if(!empty($apiKey)) {
                    $registers = VP_Api::getRegisters();
                    if($registers) {
                        $params['registersList'] = $registers;
                    }
                }

                $params['apiKey']     = $apiKey;
                $params['registerId'] = get_option('vp_form_config_register');
                $params['exemption']  = get_option('vp_form_config_exemption');
                $params['exemptionLaw']  = get_option('vp_form_config_exemption_law');
                $params['exemptionList'] = VP_Orders::getExemptions();
                $params['invoiceType']   = get_option('vp_form_config_invoice_type');
                $params['invoiceList']   = array(
                    'FT' => 'Fatura (FT)',
                    'FR' => 'Fatura Recibo (FR)',
                );

                if(!$params['registerId'] || !$params['exemption']) {
                    VP_Utils::notify('É necessario escolher uma Caixa e um Motivo da Isenção');
                }

                VP_Utils::render('config.php', $params);
            }
        }

    }

    private function _viewPdfHook()
    {
        if(!function_exists('vp_action_view_pdf')) {
            function vp_action_view_pdf($id) {
                if(!$id) {
                    die('Error: vp_action_view_pdf');
                    exit;
                }
                
                $link = VP_Api::getPdf($id);
                
                if(!$link) {
                    die('Error: Não tem permissão para ver a fatura. (api_key não é da conta onde foi gerada a fatura)');
                }

                header('Location: ' . $link);
                exit;
            }
        }
        add_action('vp_action_view_pdf', 'vp_action_view_pdf');
    }

    private function _invoiceHook()
    {
        if(!function_exists('vp_action_invoice')) {
            function vp_action_invoice($id) {
                
                $redirect = admin_url('edit.php?post_type=shop_order');
                if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                    $redirect = $_SERVER['HTTP_REFERER'];
                }

                $_order = new WC_Order($id);
                $order = $_order->get_data();
                
                $order['date_created']   = $order['date_created']->__toString();
                $order['date_modified']  = $order['date_modified']->__toString();
                $order['date_completed'] = $order['date_completed']->__toString();
                $order['date_paid']      = $order['date_paid'] ? $order['date_paid']->__toString() : '';
                
                //$order['meta_data']      = $order['meta_data'][0]->get_data();
                $metaData = array();
                foreach($order['meta_data'] as $meta) {
                    $metaData[] = $meta->get_data();
                }
                $order['meta_data'] = $metaData;
                
                $coupons = array();
                foreach($order['coupon_lines'] as $coupon) {
                    $coupons[] = $coupon->get_data();
                }
                $order['coupon_lines'] = $coupons;
                
                $items = array();
                foreach($order['line_items'] as $item) {
                    $product = wc_get_product($item->get_product_id());
                    $items[] = array_merge($item->get_data(), array('sku' => $product->get_sku()));
                }
                $order['line_items'] = $items;
                
                $taxes = array();
                foreach($order['tax_lines'] as $item) {
                    $taxes[] = $item->get_data();
                }
                $order['tax_lines'] = $taxes;
                
                $invoice = VP_Api::createInvoice($order);

                if(!$invoice['success']) {
                    wp_redirect($redirect . '&vp-error=' . urlencode($invoice['message']));
                    exit;
                }

                $_order->update_meta_data(VP_Orders::CUSTOM_FIELD_INVOICES, $invoice['message']);
                $_order->save_meta_data();
                wp_redirect($redirect . '&vp-success=1');
                exit;
            }
        }
        add_action('vp_action_invoice', 'vp_action_invoice');

        if(!function_exists('vp_action_invoice_nc')) {
            function vp_action_invoice_nc($id) {
                if(!$id && !isset($_POST['obs']) || !$_POST['obs']) {
                    die('campos não preenchidos');
                }

                $redirect = admin_url('edit.php?post_type=shop_order');
                if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                    $redirect = $_SERVER['HTTP_REFERER'];
                }

                $obs      = $_POST['obs'];
                $order    = new WC_Order($id);
                $metaList = $order->get_meta_data();
                
                foreach($metaList as $item) {
                    $meta = $item->get_data();
                    if($meta['key'] == VP_Orders::CUSTOM_FIELD_INVOICES) {
                        $invoiceData = $meta['value'];
                        break;
                    }
                }
                
                $ncData = VP_Api::createInvoiceNC($invoiceData['id'], $obs);

                if(!$ncData['success']) {
                    wp_redirect($redirect . '&vp-error=' . urlencode($invoice['message']));
                    exit;
                }

                $invoiceData['nc'] = $ncData['message'];
                $order->update_meta_data(VP_Orders::CUSTOM_FIELD_INVOICES, $invoiceData);
                $order->save_meta_data();
                
                wp_redirect($redirect . '&vp-success=1');
                exit;
            }
        }
        add_action('vp_action_invoice_nc', 'vp_action_invoice_nc');

        function general_admin_notice(){
            global $pagenow;
            
            if ( in_array($pagenow, ['edit.php', 'post.php']) ) {
                if(isset($_GET['vp-success'])) {
                    VP_Utils::notify('Emitido com sucesso!', 'success');
                } else if(isset($_GET['vp-error'])) {
                    VP_Utils::notify($_GET['vp-error'], 'error');
                }
            }
        }
        add_action('admin_notices', 'general_admin_notice');
    }

    private function _checkStatus()
    {
        if(get_option('vp_config_api_key') && get_option('vp_form_config_register') && get_option('vp_form_config_exemption')) {
            $this->_status = true;
            return true;
        }

        $this->_status = false;
        return false;
    }

    public function isActive()
    {
        return $this->_status;
    }
}