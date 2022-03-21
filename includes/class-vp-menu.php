<?php

class VP_Menu
{
    public function __construct() {

    }

    static public function run($isDev = false)
    {
        function vp_plugin_menu() {
            add_menu_page('Vendus Plugin - Opções', 'Vendus Plugin', 'manage_woocommerce', 'vp_settings', 'vp_action_config', VP_Utils::getImage('logo_menu.png'));
        }
        add_action('admin_menu', 'vp_plugin_menu');
    }
}