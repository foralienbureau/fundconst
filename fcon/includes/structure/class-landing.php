<?php if( !defined('WPINC') ) die;

/** Class to represent landing object **/

class Fcon_Landing {

    const SINGLE_PERIOD = 'single';

    const RECURRING_PERIOD = 'recurring';

    protected $post_object;

    protected $is_open;
    protected $target_type;

    protected $single_amount_settings;
    protected $natural_settings;
    protected $recurring_settings;

    protected $campaign;
    protected $collected_amount;

    protected $privacy_page;
    protected $oferta_page;


    public function __construct( $post ) {

        if( is_a( $post, 'WP_Post' )  ) {
            $this->post_object = $post;
        }
        else if( (int)$post > 0 ) {
            $this->post_object = get_post( (int)$post );
        }
    }

    public function __get( $key ) {

        switch($key) {
            case 'ID':
            case 'id':
                return $this->post_object->ID;
                break;

            case 'post_type':
                return $this->post_object->post_type;
                break;
        }

        return isset($this->post_object->$key) ? $this->post_object->$key : null;
    }


    // compatibility
    public function get_post_object() {

        return $this->post_object;
    }

    public function get_id() {

        return $this->post_object->ID;
    }   


    // logic
    public function is_open() {

        if( $this->is_open === null ) {

            $closed = (bool)get_post_meta( $this->ID, 'campaign_finished', true );

            $this->is_open = !$closed;
        }

        return $this->is_open;
    }


    // global settings 
    public function get_oferta_page() {

        if ( $this->oferta_page === null ) {
            $this->oferta_page = (int)get_option('options_oferta_page');
        }
        
        return $this->oferta_page;
    }

    public function get_oferta_link() {

        $page = $this->get_oferta_page();

        return get_permalink($page);
    }

    public function get_privacy_page() {
        
        if ( $this->privacy_page === null ) {
            $this->privacy_page = (int)get_option('options_privacy_page');
        }
        
        return $this->privacy_page;
    }

    public function get_privacy_link() {

        $page = $this->get_privacy_page();

        return get_permalink($page);
    }

    public function get_currency_label() {

        return fcon_get_setting( 'currency_label' );
    }

    public function get_currency_code() {

        return fcon_get_setting( 'currency_code' );
    }

    public function get_amount_limits() {   

        $defaults = array('min' => 10, 'max' => 100000);

        if( !function_exists('get_field') ) {
            return $defaults;
        }

        $limits = get_field('amount_limits', 'options');

        $limits = wp_parse_args( $limits, $defaults );

        $limits = array_map( 'intval', $limits );

        return $limits;
    }

    public function get_pm_settings() {

        // YM card
        $defaults = array(
            'single' => 'yandex-yandex_card',
            'recurring' => 'yandex-yandex_card',
        );

        if( !function_exists('get_field') ) {
            return $defaults;
        }

        $options = get_field('active_pm', 'options');

        return wp_parse_args( $options, $defaults );
    }

    public function is_form_nonce_disabled() {

        $opt = (int)get_option('leyka_check_nonce_on_public_donor_actions', 1);

        return $opt != 1;
    }


    // single amount hints
    protected function get_single_amount_settings() {

        if ( $this->single_amount_settings === null ) {
            $this->single_amount_settings = get_field( 'amount_variants', $this->ID );
        }

        return $this->single_amount_settings;
    }

    public function get_single_amount_variants() {

        $amount_settings = $this->get_single_amount_settings();

        $variants = fcon_get_setting( 'amount_defaults' );

        if( $amount_settings && !empty( $amount_settings ) ) {
            $count = 0;

            foreach( $amount_settings as $key => $variant ) {

                if ( $key != 'selected' ) {
                    $variants[$count] = (int)$variant;
                }

                $count++;
            }
        }

        return $variants;
    }

    public function get_single_default_amount() {

        $amount_settings = $this->get_single_amount_settings();

        if ( $amount_settings && isset( $amount_settings['selected'] ) ) {

            return (int)$amount_settings['selected'];
        }

        return fcon_get_setting( 'amount_selection' );
    }



    // recurring
    protected function get_recurring_settings() {

        if ( $this->recurring_settings === null ) {
            $this->recurring_settings = get_field( 'recurring', $this->ID );
        }

        return $this->recurring_settings;
    }

    public function has_recurring_support() {

        $target_type = $this->get_target_type();

        if( $target_type != 'none' ) {
            return false;
        }

        $recurring = $this->get_recurring_settings();

        $support = false;

        if( $recurring && isset( $recurring['supported'] ) ) {
            $support = (bool)$recurring['supported'];
        }

        return $support;
    }

    public function get_default_period() {

        if ( !$this->has_recurring_support() ) {
            return self::SINGLE_PERIOD;
        }

        $recurring = $this->get_recurring_settings();

        if( $recurring && isset( $recurring['default_period'] ) ) {
            return $recurring['default_period'];
        }

        return self::SINGLE_PERIOD;
    }

    public function get_recurring_amount_variants() {

        $recurring = $this->get_recurring_settings();

        $variants = fcon_get_setting( 'amount_defaults' );

        if ( $recurring && isset( $recurring['amount_variants'] ) ) {

            $amount_variants = $recurring['amount_variants'];

            if( !empty( $amount_variants ) ) {
                $count = 0;

                foreach( $amount_variants as $key => $variant ) {

                    if ( $key != 'selected' ) {
                        $variants[$count] = (int)$variant;
                    }

                    $count++;
                }
            }
        }

        return $variants;
    }

    public function get_recurring_default_amount() {

        $recurring = $this->get_recurring_settings();

        if ( $recurring && isset( $recurring['amount_variants'] ) ) {

            if ( isset( $recurring['amount_variants']['selected'] ) ) {
                return (int)$recurring['amount_variants']['selected'];
            }
        }

        return fcon_get_setting( 'amount_selection' );
    }



    // amount hints  for default period
    public function get_amount_variants() {

        $default_period = $this->get_default_period();

        if ( $default_period == self::RECURRING_PERIOD ) {  

            return $this->get_recurring_amount_variants();
        }

        return $this->get_single_amount_variants();
    }

    public function get_default_amount() {

        $default_period = $this->get_default_period();

        if ( $default_period == self::RECURRING_PERIOD ) {

            return $this->get_recurring_default_amount();
        }

        return $this->get_single_default_amount();
    }



    // natural
    protected function get_natural_settings() {

        if ( $this->natural_settings === null ) {
            $this->natural_settings = get_field( 'target_natural', $this->ID );
        }

        return $this->natural_settings;
    }

    public function get_natural_price() {

        $settings = $this->get_natural_settings();

        if( isset($settings['price']) && $settings['price'] ) {
            return (int)$settings['price'];
        }

        return 0;
    }

    public function get_natural_selection() {

        $settings = $this->get_natural_settings();

        if( isset($settings['selected']) && $settings['selected'] ) {
            return (int)$settings['selected'];
        }

        return 2;
    }



    // target 
    public function get_target_type() {

        if ( $this->target_type === null ) {

            $target_type = get_post_meta( $this->ID, 'target_type', true );

            if( !$target_type ) {

                $target_type = 'none';
            }

            $this->target_type = $target_type;
        }
        
        return $this->target_type;
    }

    public function has_target() {

        $target_type = $this->get_target_type();

        if ( $target_type == 'none' ) {
            return false;
        }

        // TODO other logic

        return true;
    }

    public function get_collected_amount() {

        $type = $this->get_target_type();

        if ( $type == 'none' ) {
            return 0;
        }

        return (int)$this->get_collected_amount_from_campaign();
    }

    public function get_target_amount() {

        $type = $this->get_target_type();

        $value = 0;

        if ( $type == 'amount' ) {
            
            $value = (int)get_post_meta( $this->ID, 'target_amount', true );
        }
        else if( $type == 'natural' ) {

            $settings = $this->get_natural_settings();

            $target = isset( $settings['target'] ) ? (int)$settings['target'] : 0;

            $price = isset( $settings['price'] ) ? (int)$settings['price'] : 0;

            $value = $target * $price;
        }

        return (int)$value;
    }

    public function get_collected_value() {

        $type = $this->get_target_type();

        $value = 0;

        if ( $type == 'amount' ) {

            $value = $this->get_collected_amount_from_campaign();
        }
        else if( $type == 'natural' ) {

            $amount = (int)$this->get_collected_amount_from_campaign();

            $price = $this->get_natural_price();

            $value = round( $amount / $price );
        }

        return $value;
    }

    public function get_target_value() {

        $type = $this->get_target_type();

        $value = 0;

        if ( $type == 'amount' ) {
            
            $value = (int)get_post_meta( $this->ID, 'target_amount', true );
        }
        else if( $type == 'natural' ) {

            $settings = $this->get_natural_settings();

            $value = isset( $settings['target'] ) ? (int)$settings['target'] : 0;
        }

        return (int)$value;
    }

    public function get_target_part_label( $number ) {

        $type = $this->get_target_type();

        $label = '';

        if ( $type == 'amount' ) {

            $label = $this->get_currency_label();
        }
        else if ( $type == 'natural' ) {

            $text_variants = $this->get_natural_labels(); 

            if( $text_variants && !empty( $text_variants ) ) {

                $label = $this->select_natural_label( $number, $text_variants );
            }
        }

        return trim( $label );
    }

    protected function get_natural_labels() {

        $settings = $this->get_natural_settings(); 

        $texts = array();

        if ( isset( $settings['texts'] ) && $settings['texts'] ) {
            foreach( $settings['texts'] as $variant ) {
                $texts[] = $variant;
            }
        }

        return $texts ;
    }

    protected function select_natural_label( $number, $text_variants ) {

        $number = $number % 100;

        if ( $number >= 11 && $number <= 19 ) {
            $ending = $text_variants[2];
        }
        else {
            $i = $number % 10;

            switch ($i) {
                case (1): $ending = $text_variants[0]; break;
                case (2):
                case (3):
                case (4): $ending = $text_variants[1]; break;
                default: $ending = $text_variants[2];
            }
        }

        return $ending;
    }


    // campaign
    protected function get_campaign() {

        if ( $this->campaign == null ) {

            $this->campaign = $this->get_latest_connected_campaign();
        }

        return $this->campaign;
    }

    protected function get_latest_connected_campaign() {

        //$c_id = 335; 
        $posts = get_posts( array(
            'post_type'     => fcon_get_setting( 'leyka_campaign_post_type' ),
            'post_status'   => 'private',
            'post_per_page' => 1,
            'post_parent'   => $this->ID,
            'orderby'       => array( 'date' => 'DESC')
        ));

        $campaign = null;

        if( !empty( $posts ) ) {
            $campaign = new Fcon_Leyka_Campaign( $posts[0] );
        }
        else {
            $error = fcon_get_setting( 'error_no_campaign' );
            $campaign = new WP_Error( $error );
        }

        return $campaign;
    }

    public function get_connected_campaigns() {

        $posts = get_posts( array(
            'post_type'     => fcon_get_setting( 'leyka_campaign_post_type' ),
            'post_status'   => 'private',
            'post_per_page' => 1000,
            'post_parent'   => $this->ID,
            'orderby'       => array( 'date' => 'DESC')
        ));

        return $posts;
    }

    public function has_connected_campaign() {
        
        $campaign = $this->get_campaign();

        return !is_wp_error( $campaign );
    }

    public function get_campaign_id() {

        $campaign = $this->get_campaign();

        if ( !is_wp_error( $campaign ) ) {
            return $campaign->get_id();
        }

        return 0;
    }

    public function get_payment_purpose() {

        $campaign = $this->get_campaign();

        if ( is_wp_error( $campaign ) ) {
            return '';
        }

        return $campaign->get_payment_purpose();
    }

    public function get_collected_amount_from_campaign() {

        if ( $this->collected_amount === null ) {

            $campaign = $this->get_campaign();

            if( is_wp_error( $campaign ) ) {
                $this->collected_amount = 0;
            }
            else {
                $this->collected_amount = $campaign->get_collected_amount();
            }
        }

        return $this->collected_amount; 
    }



    // thankyou page
    public function get_success_url() {

        $permalink = get_permalink( $this->get_post_object() );

        return add_query_arg('thankyou', 1, $permalink);
    }

} // class 
