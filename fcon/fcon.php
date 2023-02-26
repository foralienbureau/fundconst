<?php
/*
Plugin Name: Fundraising Constructor
Plugin URI: https://github.com/foralienbureau/
Description: Create fundraising landing pages
Version: 0.0.2
Text Domain: fcon
Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'FCON_FILE', __FILE__ );
define( 'FCON_PATH', plugin_dir_path( __FILE__ ) );
define( 'FCON_URL', plugin_dir_url( __FILE__ ) );
define( 'FCON_BASENAME', plugin_basename( __FILE__ ) );
define( 'FCON_VERSION', '0.0.1' );


function fcon_activate_plugin() {
    require_once FCON_PATH . 'includes/class-activator.php';
    Fcon_Activator::activate();
}


function fcon_deactivate_plugin() {
    require_once FCON_PATH . 'includes/class-deactivator.php';
    Fcon_Deactivator::deactivate();
}


register_activation_hook( __FILE__, 'fcon_activate_plugin' );
register_deactivation_hook( __FILE__, 'fcon_deactivate_plugin' );


require_once FCON_PATH . 'includes/class-fcon.php';


function fcon() {
    global $fcon;

    // Instantiate only once.
    if ( ! isset( $fcon ) ) {
        $fcon = new Fcon();
        $fcon->initialize();
    }

    return $fcon;
}


fcon();
