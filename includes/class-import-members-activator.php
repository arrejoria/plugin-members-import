<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/arr-dev
 * @since      1.0.0
 *
 * @package    Import_Members
 * @subpackage Import_Members/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Import_Members
 * @subpackage Import_Members/includes
 * @author     Performance Team <performanceboldt@gmail.com>
 */
class Import_Members_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::createMembersTable();

		flush_rewrite_rules(); // Make sure rewrite rules are added immediately after creating tables.
	}

	
	public static function createMembersTable(){
		global $wpdb;

		$sql = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . 'promo_members' . '` ( `id` INT NOT NULL AUTO_INCREMENT, `form_id` INT NOT NULL, `promo_status` VARCHAR(11) NOT NULL DEFAULT "inactivo", `headers` VARCHAR(255) NOT NULL, `fields` VARCHAR(255) NOT NULL, `date_update` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)); ';

		if (!$wpdb->query($sql)) return false;
		return true;
	}

}
