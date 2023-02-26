<?php
/** Fired during plugin deactivation. */

class Fcon_Deactivator {


    public static function deactivate() {

        flush_rewrite_rules();

        delete_option( 'fcon_rewrite_rules_flused' );
    }

}
