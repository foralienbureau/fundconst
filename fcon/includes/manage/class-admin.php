<?php if( !defined('WPINC') ) die;
/**
 * CSS/JS loading and related functions
 * */

class Fcon_Admin {

    private static $_instance = null;

    protected $manifest = null;

    protected $fonts_settings = null;

    protected $early_inline_styles = array();


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

        add_action('acf/save_post', array( $this, 'save_campaign_connection' ), 30 );

        add_action('add_meta_boxes', array( $this, 'correct_campaign_metaboxes' ), 30 ); 

        add_filter('acf/load_field/type=message', array( $this, 'display_connected_campaigns' ), 30 );

        // columns
        add_filter( 'manage_posts_columns', array( $this, 'columns_names' ), 50, 2);

        $leyka_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        add_filter( 'manage_'. $leyka_post_type.'_posts_columns' , array( $this, 'campaign_columns_clean'), 50, 2);

        add_action('manage_posts_custom_column', array( $this, 'columns_content' ), 2, 2);

        // filter in leyka admin
        add_filter('views_edit-leyka_campaign', array( $this, 'campaign_views_filter' ), 2, 2);

        add_action( 'admin_init', array( $this, 'custom_query_var' ) );

        add_action( 'parse_query', array( $this, 'request_correction' ) );

        // donations edit 
        add_filter(
            'leyka_donation_info_campaign_not_found_content',
            array( $this, 'campaign_correct_name' ), 
            10, 3
        );

        // force custom color support
        add_action( 'admin_init', array($this, 'theme_support_corrected') );
    }


    // Type in Leyka campaigns
    public function campaign_views_filter( $views ) {

        $show = apply_filters( 'fcon_show_campaign_views_filter', true );

        if( !$show ) {
            return $views;
        }

        $filter = admin_url('edit.php');

        $filter = add_query_arg( array(
            'post_type'     => fcon_get_setting( 'leyka_campaign_post_type' ),
            'for_landing'   => 1,
        ), $filter );

        $current = "";

        if( isset($_GET['for_landing']) && (int)$_GET['for_landing'] === 1 ) {
            $current = "class='current'";
        }

        $label = fcon_get_setting( 'campaign_type_label' );
        $views['for_landings'] = "<a href='{$filter}' {$current}>{$label}</a>";

        return $views;
    }

    public function custom_query_var() {
        global $wp;

        $wp->add_query_var('for_landing');
    }

    public function request_correction( WP_Query $query ) {

        if( !is_admin() ) {
            return;
        }

        $leyka_campaign_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if( $query->is_main_query() && $leyka_campaign_post_type == $query->get('post_type') ) {
            $test = (int)$query->get('for_landing');

            if ( $test === 1 ) {
                $query->set( 'post_parent__not_in', array(0) );
            }
        }
    }

    public function campaign_correct_name( $html, $donation, $campaign ) {

        $c_post = get_post( $donation->campaign_id );

        if( $c_post->post_parent > 0 && $c_post->post_status == 'private' ) {
            $html = sprintf( "<span class='text-line'>%s</span>", $c_post->post_title );
        }
        
        return $html;
    }


    // Columns in landings
    public function columns_names( $columns, $post_type ) {
    
        $landing_post_type = fcon_get_setting('post_type');

        $leyka_campaign_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if( $post_type ==  $landing_post_type ) {
            $columns['collection_status'] = 'Статус сбора';
            $columns['collection_progress'] = 'Прогресс';
            $columns['campaign'] = 'Кампания';
            
            if( !isset( $columns['author']) ) {
                $columns['author'] = 'Создал';
            }   
        }
        else if( $post_type == $leyka_campaign_post_type ) {

            $columns['fcon_shortcode'] = 'Шорткод кампании';
            $columns['fcon_landing'] = 'Лендинг';
        }
        
        return $columns;
    }

    public function campaign_columns_clean( $columns ) {

        if( isset( $columns['shortcode'] ) ) {
            unset( $columns['shortcode'] );
        }

        return $columns;
    }

    public function columns_content( $column_name, $post_id ) {

        $cpost = get_post( $post_id );

        $landing_post_type = fcon_get_setting('post_type');

        $leyka_campaign_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if( $cpost->post_type == $landing_post_type ) {

            $this->print_landing_table_columns( $column_name, $cpost );
        }
        else if( $cpost->post_type == $leyka_campaign_post_type ) {

            $this->print_campaign_table_columns( $column_name, $cpost );
        }
    }

    protected function print_landing_table_columns( $column_name, $landing_post ) {

        $landing = new Fcon_Landing( $landing_post );

        if( $column_name == 'collection_status' ) {
            
            echo $landing->is_open() ? 'Открыт': 'Закрыт';
        }
        else if( $column_name == 'collection_progress' ) {
            
            $target = new Fcon_Target_Admin_Template( $landing );

            $target->print_markup();
        }
        else if( $column_name == 'campaign' ) {

            $campaigns = $landing->get_connected_campaigns();

            if( !$campaigns ) {
                echo '-';
            }
            else {
                printf(
                    "%s (<a href='%s'>ID: %s</a>)",
                    $campaigns[0]->post_title,
                    get_edit_post_link( $campaigns[0]->ID ),
                    $campaigns[0]->ID
                );
            }
        }
    }

    protected function print_campaign_table_columns( $column_name, $campaign_post ) {

        if( $column_name == 'fcon_shortcode' ) {

            if( $campaign_post->post_parent > 0 ) {
                echo '-';
            }
            else { ?>
                <input type="text" class="embed-code read-only campaign-shortcode" value="<?php echo esc_attr(Leyka_Campaign_Management::get_campaign_form_shortcode( $campaign_post->ID ) );?>">
            <?php }
        }
        else if( $column_name == 'fcon_landing' ) {

            if( $campaign_post->post_parent > 0 ) {
            
                $landing_id = (int)$campaign_post->post_parent;

                printf(
                    "%s (<a href='%s'>ID: %s</a>)",
                    get_the_title($landing_id),
                    get_edit_post_link( $landing_id ),
                    $landing_id
                );
            }
            else {
                echo '-';
            }
        }
    }   


    // Campaign
    public function display_connected_campaigns( $field  ) {

        if( !isset( $field['message'] ) || empty($field['message']) ) {
            return $field;
        }

        if( false === strpos( $field['message'], '[connected_leyka]' ) ) {
            return $field;
        }   

        $landing_id = isset( $_GET['post'] ) ? (int)$_GET['post'] : 0;

        if( $landing_id == 0 ) {
            return $field;
        }

        $field['message'] = $this->get_connected_campaigns_html( $landing_id );

        return $field;
    }

    protected function get_connected_campaigns_html( $landing_id ) {

        $landing = new Fcon_Landing( $landing_id );

        $campaigns = $landing->get_connected_campaigns();

        if( empty( $campaigns ) ) { 

            return "<div class='fcon-empty'>".fcon_get_setting( 'error_no_campaign')."</div>";
        }

        $list = array();

        foreach( $campaigns as $cp ) {

            $campaign = new Fcon_Leyka_Campaign( $cp );
            $list[] = $campaign->get_connection_row_html();
        }

        $out = "<ul class='fcon-connections'>";
        $out .= implode('', $list);
        $out .= "</ul>";
        
        return $out; 
    }

    public function save_campaign_connection( $post_id ) {

        $post = get_post( $post_id );

        $leyka_campaign_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if( $post->post_type != $leyka_campaign_post_type ) { 
            return;
        }

        $landing_type = (int)get_field( 'landing_type', $post_id );
        
        if( $landing_type === 1 ) {
            // update connection

            $connection = get_field( 'landing', $post_id );
        
            if( $connection ) {

                $postarr = array(
                    'ID' => $post_id,
                    'post_parent' => (int)$connection,
                    'post_status' => 'private'
                );

                wp_update_post( $postarr, false, false );

                $this->clean_landing_campaign_meta( $post_id );
            }
        }
        else {
            
            if( $post->post_parent > 0 ) {
                // remove connection - if there was one

                $postarr = array(
                    'ID' => $post_id,
                    'post_parent' => 0,
                    'post_status' => 'draft'
                );

                wp_update_post( $postarr, false, false );
            }

        }
        
    }

    protected function clean_landing_campaign_meta( $post_id ) {

        $purpose = get_field( 'landing_payment_purpose', $post_id );

        update_post_meta( $post_id, 'payment_title', $purpose  );

        update_post_meta( $post_id, 'campaign_target', '' ) ;

        update_post_meta( $post_id, 'is_finished', 0 );
    }

    public function correct_campaign_metaboxes() {

        $post = get_post();

        if( !$post ) {
            return;
        }

        $leyka_campaign_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if( $post->post_type != $leyka_campaign_post_type ) {
            return;
        }

        if( $post->post_parent == 0 ) {
            return; // no landing connection
        }

        remove_meta_box(
            "{$leyka_campaign_post_type}_excerpt",
            $leyka_campaign_post_type,
            'normal'
        );

        remove_meta_box(
            "{$leyka_campaign_post_type}_data",
            $leyka_campaign_post_type,
            'normal'
        );

        remove_meta_box(
            "{$leyka_campaign_post_type}_payments_amounts",
            $leyka_campaign_post_type,
            'normal'
        );

        remove_meta_box(
            "{$leyka_campaign_post_type}_additional_fields",
            $leyka_campaign_post_type,
            'normal'
        );
    }


    //  color support for landings blocks
    public function theme_support_corrected() {

        $post_type = fcon_get_setting('post_type');
        $current_post = ( isset( $_GET['post'] ) ) ? (int) $_GET['post'] : 0;

        if( $current_post == 0 ) {
            return;
        }

        $current_post_type = get_post_type( $current_post );

        if( $current_post_type == $post_type ) {
            remove_theme_support( 'disable-custom-colors' );

            fcon_log('disable');
        }
    }
    
} // class 
