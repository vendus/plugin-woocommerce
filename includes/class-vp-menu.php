<?php

class Vendus_Plugin_Menu
{
    public function __construct() {

    }

    static public function run($isDev = false)
    {
        function vendus_plugin_admin_menu() {
            add_menu_page('Vendus Plugin - Opções', 'Vendus Plugin', 'manage_woocommerce', 'vendus_plugin_settings', 'vendus_plugin_action_config', Vendus_Plugin_Utils::getImage('logo_menu.png'));
        }
        add_action('admin_menu', 'vendus_plugin_admin_menu');
    }
}