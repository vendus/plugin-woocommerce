<?php
/**
* Plugin Name: Vendus
* Plugin URI: https://www.vendus.pt/
* Description: Integração Woocommerce com o Vendus.
* Version: 2.0
* Author: vendus
* Author URI: https://nex.pt/
**/

define('VENDUS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VENDUS_PLUGIN_DEV', false);

if (!class_exists('VENDUS_PLUGIN', false)) {
	require_once VENDUS_PLUGIN_PATH . 'includes/class-vp.php';
}

function initVP() { 
	return Vendus_Plugin::instance();
}

$GLOBALS['VENDUS_PLUGIN_GLOBAL'] = initVP();

if(VENDUS_PLUGIN_DEV) {
    function pr($arr) 
    {
        echo "<div style='background:#eee;border:1px solid #ccc;font-size:12px;padding:10px;margin-bottom:20px'>";
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
        echo "</div>";
    }
}