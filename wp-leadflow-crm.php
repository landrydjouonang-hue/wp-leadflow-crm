<?php
/**
 * WP LeadFlow CRM
 *
 * @package           LeadFlow\CRM
 * @author            Djouonang Landry
 * @copyright         2026 Djouonang Landry
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP LeadFlow CRM
 * Description:       CRM &amp; lead management platform for WordPress. Manage leads, companies, contacts, sales stages, activities, tasks, follow-ups, notes and customer communication.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            Djouonang Landry
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-leadflow-crm
 * Domain Path:       /languages
 */

/*
 * This file must stay parseable by old PHP versions so the requirements
 * notice can be shown instead of a fatal error. Keep modern syntax in src/.
 */

defined( 'ABSPATH' ) || exit;

define( 'LEADFLOW_CRM_VERSION', '1.0.0' );
define( 'LEADFLOW_CRM_FILE', __FILE__ );
define( 'LEADFLOW_CRM_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEADFLOW_CRM_URL', plugin_dir_url( __FILE__ ) );
define( 'LEADFLOW_CRM_BASENAME', plugin_basename( __FILE__ ) );
define( 'LEADFLOW_CRM_MIN_PHP', '8.0' );
define( 'LEADFLOW_CRM_MIN_WP', '6.4' );

require_once LEADFLOW_CRM_PATH . 'src/Core/Requirements.php';

$leadflow_crm_requirements = new LeadFlow\CRM\Core\Requirements( LEADFLOW_CRM_MIN_PHP, LEADFLOW_CRM_MIN_WP );

if ( ! $leadflow_crm_requirements->met() ) {
	$leadflow_crm_requirements->register_notice();
	unset( $leadflow_crm_requirements );
	return;
}

unset( $leadflow_crm_requirements );

require_once LEADFLOW_CRM_PATH . 'src/Autoloader.php';

LeadFlow\CRM\Autoloader::register( 'LeadFlow\\CRM\\', LEADFLOW_CRM_PATH . 'src/' );

register_activation_hook( __FILE__, array( 'LeadFlow\\CRM\\Core\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LeadFlow\\CRM\\Core\\Deactivator', 'deactivate' ) );

/**
 * Returns the main plugin instance.
 *
 * @return LeadFlow\CRM\Plugin
 */
function leadflow_crm() {
	return LeadFlow\CRM\Plugin::instance();
}

add_action( 'plugins_loaded', array( leadflow_crm(), 'boot' ) );
