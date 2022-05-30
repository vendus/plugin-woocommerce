<?php

class Vendus_Plugin_Utils
{
    static public function getImage($filename) {
        return VENDUS_PLUGIN_URL . 'assets/images/' . $filename;
    }
    static public function getCss($filename) {
        return VENDUS_PLUGIN_URL . 'assets/css/' . $filename;
    }
    static public function getJs($filename) {
        return VENDUS_PLUGIN_URL . 'assets/js/' . $filename;
    }

    static public function render($view, $params = array())
    {
        extract($params);
        include(VENDUS_PLUGIN_VIEW_PATH . $view);
    }

    static public function notify($msg, $type='warning') {
        echo '<div class="notice notice-'.esc_attr($type).' is-dismissible"><p>'.esc_html($msg).'</p></div>';
    }
}