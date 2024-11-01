<?php
/**
 * Plugin Name:       Uploads for WooCommerce
 * Plugin URI:        https://woouploads.com
 * Description:       Let customers upload files to your WooCommerce products. Perfect for online photo or document processing services. Developer friendly.
 * Version:           1.0.2
 * Requires at least: 5.0
 * Requires PHP:      7.1.1
 * Author:            Mujnoi Tamas
 * Author URI:        https://europadns.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       uploads-for-woocommerce
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) or exit;

defined( 'WOO_UPLOADS_VERSION' ) or define( 'WOO_UPLOADS_VERSION', '1.0.2' );
defined( 'WOO_UPLOADS_DIR' ) or define( 'WOO_UPLOADS_DIR', plugin_dir_path( __FILE__ ) );
defined( 'WOO_UPLOADS_URL' ) or define( 'WOO_UPLOADS_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/autoload.php';

\WooUploads\App::init();
