<?php
/**
 * Runs when the plugin is deleted from the Plugins screen.
 *
 * Data is only removed when "Delete all CRM data when the plugin is
 * deleted" is enabled (Settings > Advanced) or when
 * define( 'LEADFLOW_CRM_REMOVE_ALL_DATA', true ) is set in wp-config.php.
 *
 * @package LeadFlow\CRM
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/src/Autoloader.php';

LeadFlow\CRM\Autoloader::register( 'LeadFlow\\CRM\\', __DIR__ . '/src/' );

LeadFlow\CRM\Core\Uninstaller::uninstall();
