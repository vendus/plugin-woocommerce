<?php

class VP_Api
{
    public function __construct() {

    }

    static public function createInvoice($order)
    {
        $params = array(
            'order'         => $order,
            'register_id'   => get_option('vp_form_config_register'),
            'exemption'     => get_option('vp_form_config_exemption'),
            'exemption_law' => get_option('vp_form_config_exemption_law'),
            'invoice_type'  => get_option('vp_form_config_invoice_type'),
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
        $apiKey = get_option('vp_config_api_key');
        $url    = VENDUS_URL . $endpoint . '/?api_key=' . $apiKey;
        $params['api_key'] = $apiKey;
        
        $content = json_encode($params);
        $curl    = curl_init($url);
        //pr($content);exit;
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array(
                "Content-type: application/json",
                "Content-Length: " . strlen($content),
            )
        );
        $result = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($result, true);
    }
}