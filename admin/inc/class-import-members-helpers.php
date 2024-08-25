<?php

/**
 * Funciones facilitadoras
 * 
 */


class Import_Members_Helper
{




    public static function set_error_message($message)
    {

        $class = 'notice notice-error';
        $message = __($message, 'import-members');

        // printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        return printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}
