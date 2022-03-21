<?php
/**
* Plugin Name: Vendus
* Plugin URI: https://www.vendus.pt/
* Description: Integração Woocommerce com o Vendus.
* Version: 2.0
* Author: vendus
* Author URI: https://nex.pt/
**/

define('VP_PATH', plugin_dir_path(__FILE__));
define('VP_DEV', false);

if (!class_exists('VP', false)) {
	require_once VP_PATH . 'includes/class-vp.php';
}

function initVP() { 
	return VP::instance();
}

$GLOBALS['vp'] = initVP();

if(VP_DEV) {
    function pr($arr) 
    {
        echo "<div style='background:#eee;border:1px solid #ccc;font-size:12px;padding:10px;margin-bottom:20px'>";
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
        echo "</div>";
    }
}