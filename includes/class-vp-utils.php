<?php

class VP_Utils
{
    static public function getImage($filename) {
        return VP_URL . 'assets/images/' . $filename;
    }
    static public function getCss($filename) {
        return VP_URL . 'assets/css/' . $filename;
    }
    static public function getJs($filename) {
        return VP_URL . 'assets/js/' . $filename;
    }

    static public function render($view, $params = array())
    {
        extract($params);
        include(VP_VIEW_PATH . $view);
    }

    static public function notify($msg, $type='warning') {
        echo '<div class="notice notice-'.$type.' is-dismissible"><p>'.$msg.'</p></div>';
    }
}