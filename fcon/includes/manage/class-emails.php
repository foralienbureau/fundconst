<?php if( !defined('WPINC') ) die;
/**
 * Email customization
 * */

class Fcon_Emails {

    private static $_instance = null;


    private function __construct() {

    }

    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function add_filters() {

        // single email
        add_filter( 'leyka_email_thanks_title', array( $this, 'email_thankyou_title' ), 10, 3 );
        add_filter( 'leyka_email_thanks_text', array( $this, 'email_thankyou_text' ), 10, 3 );

    }

    public function email_thankyou_title( $text, $donation, $campaign ) {

        $landing_id = get_post($campaign->ID)->post_parent;

        if( $landing_id == 0 ) {
            return $text;
        }

        $custom_text = $this->get_custom_email_title( $donation, $landing_id );

        if( $custom_text ) {
            $text = trim( $custom_text );
        }

        return $text;
    }

    public function email_thankyou_text( $text, $donation, $campaign ) {

        $landing_id = get_post($campaign->ID)->post_parent;

        if( $landing_id == 0 ) {
            return $text;
        }

        $custom_text = $this->get_custom_email_text( $donation, $landing_id );

        if( $custom_text ) {
            $text = trim( $custom_text );
        }

        return $text;
    }


    protected function get_custom_email_title( $donation, $landing_id ) {

        $custom_text = "";

        if( $donation->type === 'single' || $donation->type === 'correction' ) {
            // single 
            $custom_text = get_post_meta(  $landing_id, 'single_donation_letter_title', true);
        }
        else if( $donation->is_init_recurring_donation ) {
            // init recurring
            $custom_text = get_post_meta(  $landing_id, 'initial_recurrent_letter_title', true);
        }
        else if ( $donation->type === 'rebill' ) {
            // rebill
            $custom_text = get_post_meta(  $landing_id, 'rebill_letter_title', true);
        }

        return $custom_text;
    }

    protected function get_custom_email_text( $donation, $landing_id ) {

        $custom_text = "";

        if( $donation->type === 'single' || $donation->type === 'correction' ) {
            // single 
            $custom_text = get_post_meta(  $landing_id, 'single_donation_letter_text', true);
        }
        else if( $donation->is_init_recurring_donation ) {
            // init recurring
            $custom_text = get_post_meta(  $landing_id, 'initial_recurrent_letter_text', true);
        }
        else if ( $donation->type === 'rebill' ) {
            // rebill
            $custom_text = get_post_meta(  $landing_id, 'rebill_letter_text', true);
        }

        return $custom_text;
    }

} // class
