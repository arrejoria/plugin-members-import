<?php

/**
 * Todo tipo de consultas a la base de datos
 */

namespace IMQuery;

use Import_Members_Helper;

class Import_Members_Queries
{

    public static $table_name;
    private static $_instance = null;
    private static $helpers;
    public function __construct()
    {
        global $wpdb;

        self::$helpers = new Import_Members_Helper;
        self::$table_name = $wpdb->prefix . 'promo_members';
    }

    /**
     * Verificar si hay datos existentes en alguna columna
     */
    public static function column_duplicated_data($column, $data)
    {
        global $wpdb;

        $column_exist = self::verify_columns($column);
        if (!$column_exist) return self::$helpers->set_error_message('Atención la tabla ' . self::$table_name . ' parece haber sido eliminada. Volver a activar el plugin para crearla.');

        // Preparar datos antes de realizar la consulta
        $prepared_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . self::$table_name . " WHERE $column LIKE %s",
            '%' . $wpdb->esc_like($data) . '%'
        );

        // Ejecuta la consulta
        $count = $wpdb->get_var($prepared_query);

        // Procesa el resultado
        return $count > 0 ? true : false;
    }

    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Import_Members_Queries();
        }

        return self::$_instance;
    }


    private static function verify_columns($column)
    {
        global $wpdb;

        // Verifica si la columna existe
        $columns = $wpdb->get_col("DESC {$wpdb->prefix}promo_members");
        if (in_array($column, $columns)) {
            return $column;
        }

        return false;
    }
}
