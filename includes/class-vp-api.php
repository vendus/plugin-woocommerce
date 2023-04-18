<?php

class Vendus_Plugin_Api
{
    public function __construct() {

    }

    static public function createInvoice($order)
    {
        $params = array(
            'order'         => $order,
            'register_id'   => get_option('vendus_plugin_form_config_register'),
            'exemption'     => get_option('vendus_plugin_form_config_exemption'),
            'exemption_law' => get_option('vendus_plugin_form_config_exemption_law'),
            'invoice_type'  => get_option('vendus_plugin_form_config_invoice_type'),
            'ignore_notes'  => get_option('vendus_plugin_form_config_ignore_notes'),
            'version'       => WC_VERSION
        );
        
        $result = self::request('invoice', $params);
        
        return $result;
    }

    static public function createInvoiceNC($order, $obs)
    {
        $params = array(
            'document' => $order,
            'obs'      => $obs,
            'version'  => WC_VERSION
        );
        
        $result = self::request('invoicenc', $params);
        
        return $result;
    }

    static public function getRegisters()
    {
        $result = self::request('registers', array());
        if(isset($result['message']) && isset($result['message']['registers']) && !empty($result['message']['registers'])) {
            return $result['message']['registers'];
        }
        return array();
    }

    static public function getTaxExemptions()
    {
        $result = self::request('taxexemptions', array());
        if(isset($result['message']) && isset($result['message']['exemptions']) && !empty($result['message']['exemptions'])) {
            return $result['message']['exemptions'];
        }
        return array();
    }

    static public function getPdf($id)
    {
        $params = array(
            'id' => $id
        );
        $result = self::request('pdf', $params);
        return $result['message']['pdf'];
    }

    static public function request($endpoint, $params)
    {
        $apiKey = get_option('vendus_plugin_config_api_key');
        $url    = VENDUS_URL . $endpoint . '/?api_key=' . $apiKey;
        $params['api_key'] = $apiKey;
        
        $content = json_encode($params);

        $response = wp_remote_post($url, [
            'method'   => 'POST',
            'blocking' => true,
            'headers'  => [
                "Content-type: application/json",
                "Content-Length: " . strlen($content)
            ],
            'body' => $content
        ]);

        $response = wp_remote_retrieve_body($response);

        return json_decode($response, true);
    }
}