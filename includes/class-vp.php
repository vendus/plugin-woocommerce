<?php

class Vendus_Plugin
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

        Vendus_Plugin_Menu::run();

        if(!$this->isActive()) {
            $this->_configPage();
            return;
        }
        
        Vendus_Plugin_Orders::run();
        Vendus_Plugin_Clients::run();

        $this->initPages();
    }

    public function initConstant()
    {
        $isDev = VENDUS_PLUGIN_DEV;

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

        define('VENDUS_PLUGIN_URL', plugins_url('/', dirname(__FILE__)));
        
        //define('VENDUS_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('VENDUS_PLUGIN_VIEW_PATH', VENDUS_PLUGIN_PATH . 'views/');
        define('VENDUS_PLUGIN_IMG_PATH', VENDUS_PLUGIN_PATH . 'views/');
        define('VENDUS_PLUGIN_CSS_PATH', VENDUS_PLUGIN_PATH . 'views/');
        define('VENDUS_PLUGIN_JS_PATH', VENDUS_PLUGIN_PATH . 'views/');

    }

    public function initRequire()
    {
        require_once VENDUS_PLUGIN_PATH . 'includes/class-vp-utils.php';
        require_once VENDUS_PLUGIN_PATH . 'includes/class-vp-menu.php';
        require_once VENDUS_PLUGIN_PATH . 'includes/class-vp-orders.php';
        require_once VENDUS_PLUGIN_PATH . 'includes/class-vp-api.php';
        require_once VENDUS_PLUGIN_PATH . 'includes/class-vp-clients.php';
    }
    
    public function initPages()
    {
        $this->_configPage();

        $this->_viewPdfHook();
        $this->_invoiceHook();
    }


    private function _configPage()
    {
        if(!function_exists('vendus_plugin_action_config')) {
            function vendus_plugin_action_config() {

                if(isset($_GET['action']) && !empty($_GET['action'])) {
                    $id = isset($_GET['id']) ? sanitize_key($_GET['id']) : '';
                    do_action(sanitize_file_name($_GET['action']), $id);
                    exit;
                }

                if(isset($_POST) && !empty($_POST)) {
                    update_option('vendus_plugin_config_api_key', '');
                    update_option('vendus_plugin_form_config_register', '');
                    update_option('vendus_plugin_form_config_exemption', '');
                    update_option('vendus_plugin_form_config_exemption_law', '');
                    
                    if(isset($_POST['vendus_plugin_config_api_key'])) {
                        update_option('vendus_plugin_config_api_key', sanitize_key($_POST['vendus_plugin_config_api_key']));
                    }

                    if(isset($_POST['vendus_plugin_form_config_register'])) {
                        update_option('vendus_plugin_form_config_register', sanitize_key($_POST['vendus_plugin_form_config_register']));
                    }

                    if(isset($_POST['vendus_plugin_form_config_exemption'])){
                        update_option('vendus_plugin_form_config_exemption', strtoupper(sanitize_key($_POST['vendus_plugin_form_config_exemption'])));
                    }
                    if(isset($_POST['vendus_plugin_form_config_exemption_law'])){
                        update_option('vendus_plugin_form_config_exemption_law', sanitize_text_field($_POST['vendus_plugin_form_config_exemption_law']));
                    }

                    if(isset($_POST['vendus_plugin_form_config_invoice_type'])) {
                        update_option('vendus_plugin_form_config_invoice_type', sanitize_text_field($_POST['vendus_plugin_form_config_invoice_type']));
                    }

                    if(isset($_POST['vendus_plugin_form_config_ignore_notes'])) {
                        update_option('vendus_plugin_form_config_ignore_notes', sanitize_text_field($_POST['vendus_plugin_form_config_ignore_notes']));
                    }else{
                        update_option('vendus_plugin_form_config_ignore_notes', 0);
					}

                    if(isset($_POST['vendus_plugin_form_config_ignore_check_completed'])) {
                        update_option('vendus_plugin_form_config_ignore_check_completed', sanitize_text_field($_POST['vendus_plugin_form_config_ignore_check_completed']));
                    }else{
                        update_option('vendus_plugin_form_config_ignore_check_completed', 0);
					}

                    Vendus_Plugin_Utils::notify('Dados atualizados com suceso!', 'success');
                }
        
                $params = array();
                $apiKey = get_option('vendus_plugin_config_api_key');
                if(!empty($apiKey)) {
                    $registers = Vendus_Plugin_Api::getRegisters();
                    if($registers) {
                        $params['registersList'] = $registers;
                    }
                }

                $params['apiKey']        = $apiKey;
                $params['registerId']    = get_option('vendus_plugin_form_config_register');
                $params['exemption']     = get_option('vendus_plugin_form_config_exemption');
                $params['exemptionLaw']  = get_option('vendus_plugin_form_config_exemption_law');
                $params['exemptionList'] = Vendus_Plugin_Orders::getExemptions();
                $params['ignoreNotes']   = get_option('vendus_plugin_form_config_ignore_notes');
                $params['invoiceType']   = get_option('vendus_plugin_form_config_invoice_type');
                $params['ignoreCheck']   = get_option('vendus_plugin_form_config_ignore_check_completed');
                $params['invoiceList']   = array(
                    'FT' => 'Fatura (FT)',
                    'FR' => 'Fatura Recibo (FR)',
                );

                if(!$params['registerId'] || !$params['exemption']) {
                    Vendus_Plugin_Utils::notify('É necessário escolher uma Caixa e um Motivo da Isenção');
                }

                Vendus_Plugin_Utils::render('config.php', $params);
            }
        }

    }

    private function _viewPdfHook()
    {
        if(!function_exists('vendus_plugin_action_view_pdf')) {
            function vendus_plugin_action_view_pdf($id) {
                if(!$id) {
                    die('Error: vendus_plugin_action_view_pdf');
                    exit;
                }
                
                $link = Vendus_Plugin_Api::getPdf($id);
                
                if(!$link) {
                    die('Error: Não tem permissão para ver a fatura. (api_key não é da conta onde foi gerada a fatura)');
                }

                header('Location: ' . $link);
                exit;
            }
        }
        add_action('vendus_plugin_action_view_pdf', 'vendus_plugin_action_view_pdf');
    }

    private function _invoiceHook()
    {
        if(!function_exists('vendus_plugin_action_invoice')) {
            function vendus_plugin_action_invoice($id) {
                
                $redirect = admin_url('edit.php?post_type=shop_order');
                if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                    $redirect = sanitize_url($_SERVER['HTTP_REFERER']);
                }

                $_order = new WC_Order($id);
                $order = $_order->get_data();
                
                $order['date_created']   = $order['date_created']->__toString();
                $order['date_modified']  = $order['date_modified']->__toString();
                $order['date_completed'] = $order['date_completed'] ? $order['date_completed']->__toString() : '';
                $order['date_paid']      = $order['date_paid']      ? $order['date_paid']->__toString()      : '';
                
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
                    $items[] = array_merge(
                        $item->get_data(), 
                        ['sku' => $product->get_sku()],
                        ['invoice_description' => $product->get_attribute('invoice_description')]
                    );
                }
                $order['line_items'] = $items;
                
                $taxes = array();
                foreach($order['tax_lines'] as $item) {
                    $taxes[] = $item->get_data();
                }
                $order['tax_lines'] = $taxes;
                
                $invoice = Vendus_Plugin_Api::createInvoice($order);

                if(!$invoice['success']) {
                    wp_redirect($redirect . '&vp-error=' . sanitize_url($invoice['message']));
                    exit;
                }

                $_order->update_meta_data(Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES, $invoice['message']);
                $_order->save_meta_data();
                wp_redirect($redirect . '&vp-success=1');
                exit;
            }
        }
        add_action('vendus_plugin_action_invoice', 'vendus_plugin_action_invoice');

        if(!function_exists('vendus_plugin_action_invoice_nc')) {
            function vendus_plugin_action_invoice_nc($id) {
                if(!$id && !isset($_POST['obs']) || !$_POST['obs']) {
                    die('campos não preenchidos');
                }

                $redirect = admin_url('edit.php?post_type=shop_order');
                if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                    $redirect = sanitize_url($_SERVER['HTTP_REFERER']);
                }

                $obs      = wc_clean($_POST['obs']);
                $order    = new WC_Order($id);
                $metaList = $order->get_meta_data();
                
                foreach($metaList as $item) {
                    $meta = $item->get_data();
                    if($meta['key'] == Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES) {
                        $invoiceData = $meta['value'];
                        break;
                    }
                }
                
                $ncData = Vendus_Plugin_Api::createInvoiceNC($invoiceData['id'], $obs);

                if(!$ncData['success']) {
                    wp_redirect($redirect . '&vp-error=' . sanitize_url($invoice['message']));
                    exit;
                }

                $invoiceData['nc'] = $ncData['message'];
                $order->update_meta_data(Vendus_Plugin_Orders::CUSTOM_FIELD_INVOICES, $invoiceData);
                $order->save_meta_data();
                
                wp_redirect($redirect . '&vp-success=1');
                exit;
            }
        }
        add_action('vendus_plugin_action_invoice_nc', 'vendus_plugin_action_invoice_nc');

        function general_admin_notice(){
            global $pagenow;
            
            if ( in_array($pagenow, ['edit.php', 'post.php']) ) {
                if(isset($_GET['vp-success'])) {
                    Vendus_Plugin_Utils::notify('Emitido com sucesso!', 'success');
                } else if(isset($_GET['vp-error'])) {
                    Vendus_Plugin_Utils::notify(esc_textarea($_GET['vp-error']), 'error');
                }
            }
        }
        add_action('admin_notices', 'general_admin_notice');
    }

    private function _checkStatus()
    {
        if(get_option('vendus_plugin_config_api_key') && get_option('vendus_plugin_form_config_register') && get_option('vendus_plugin_form_config_exemption')) {
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