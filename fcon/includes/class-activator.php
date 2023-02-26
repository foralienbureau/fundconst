<?php
/** Fired during plugin activation. */

class Fcon_Activator {

    public static function activate() {

        delete_option( 'fcon_rewrite_rules_flused' );
    }

}
