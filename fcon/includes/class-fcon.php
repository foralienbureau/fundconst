<?php if( !defined('WPINC') ) die;

class Fcon {

    protected $settings;

    protected $printed_js = array();

    function __construct() {
        // Do nothing. 
    }

    public function initialize() {

        $this->settings = array(
            'slug'                      => 'fcon',
            'version'                   => FCON_VERSION,
            'basename'                  => FCON_BASENAME,
            'path'                      => FCON_PATH,
            'file'                      => FCON_FILE,
            'url'                       => FCON_URL,
            'capability'                => 'manage_options',
            'post_type'                 => 'fconland',
            'textdomain'                => 'fcon',
            'amount_defaults'           => array(300, 500, 1000, 2000),
            'amount_selection'          => 500,
            'currency_code'             => 'rub',
            'leyka_campaign_post_type'  => 'leyka_campaign',            
        );

        // Load files 
        require_once FCON_PATH . 'includes/functions.php';
        require_once FCON_PATH . 'includes/manage/class-acf.php';
        require_once FCON_PATH . 'includes/manage/class-cssjs.php';
        require_once FCON_PATH . 'includes/manage/class-emails.php';

        require_once FCON_PATH . 'includes/structure/class-blockbase.php';
        require_once FCON_PATH . 'includes/structure/class-landing.php';
        require_once FCON_PATH . 'includes/structure/class-campaign.php';
        require_once FCON_PATH . 'includes/structure/class-donation-form.php';
        
        require_once FCON_PATH . 'donations/trait-title.php';
        require_once FCON_PATH . 'donations/trait-period.php';
        require_once FCON_PATH . 'donations/trait-amount.php';
        require_once FCON_PATH . 'donations/trait-naturals.php';
        require_once FCON_PATH . 'donations/trait-inputs.php';
        require_once FCON_PATH . 'donations/trait-agree.php';
        require_once FCON_PATH . 'donations/trait-news.php';

        require_once FCON_PATH . 'donations/form-base.php';
        require_once FCON_PATH . 'donations/form-full.php';
        require_once FCON_PATH . 'donations/form-compact.php';

        require_once FCON_PATH . 'donations/target.php';


        // Include admin
        if ( is_admin() ) {
            require_once FCON_PATH . 'includes/manage/class-admin.php';

            Fcon_Admin::get_instance()->add_filters();
        }

        $this->add_filters();
    }


    // filters
    protected function add_filters() {

        add_action( 'init', array( $this, 'init' ), 50 );

        add_filter( 'template_include', array( $this, 'load_landing_template' ) );

        add_action( 'template_redirect', array( $this, 'campaign_to_landing_redirect'), 5);

        add_filter( 'leyka_yandex_custom_payment_data', array( $this, 'yandex_return_url' ), 10, 3 );

        add_filter( 'leyka_new_donation_specific_data', array( $this, 'correct_donor_news_status' ) , 10, 3 );
        
        add_action( 'wp_head', array( $this, 'add_metatags' ), 50 );

        // modules
        Fcon_Cssjs::get_instance()->add_filters();

        Fcon_Acf::get_instance()->add_filters();

        Fcon_Emails::get_instance()->add_filters();
    }

    public function init() {

        if ( ! did_action( 'plugins_loaded' ) ) {
            return;
        }

        $this->load_textdomain();

        $this->register_post_type();

        $this->flush_permalinks();

        do_action( 'fcon/init', $this->get_setting('version') );
    }


    // settings
    public function get_setting( $name ) {

        return isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : null;
    }

    public function get_text_setting( $name ) {

        $text = array(
            'name'                  => __( 'Fundraising Constructor', 'fcon' ),
            'description'           => __( 'Create fundraising landing pages.', 'fcon' ),
            'currency_label'        => __( 'rub.', 'fcon' ),
            'campaign_type_label'   => __( 'For landing', 'fcon' ),
            'error_no_campaign'     => 'Ни одна кампания не связана с лендингом. Сбор средств невозможенен',
            'default_purpose'       => 'Пожертвование на уставную деятельность',
        );

        return isset( $text[ $name ] ) ? $text[ $name ] : null;
    }


    // printed js helpers
    public function register_printed_js( $handler ) {

        if( !in_array( $handler, $this->printed_js ) ) {
            $this->printed_js[] = $handler;
        }
    }

    public function is_registered_printed_js( $handler ) {

        if( in_array( $handler, $this->printed_js ) ) {
            return true;
        }

        return false;
    }


    // post types 
    protected function register_post_type() {

        $pt_labels = array(
            'name'                  => "Лендинги",
            'singular_name'         => "Лендинг",
            'menu_name'             => "Лендинги",
            'name_admin_bar'        => "Лендинг",
            'archives'              => "Лендинги",
            'attributes'            => "Атрибуты",
            'parent_item_colon'     => "Родительский лендинг:",
            'all_items'             => "Все лендинги",
            'add_new_item'          => "Добавить новый",
            'add_new'               => "Добавить новый",
            'new_item'              => "Новый лендинг",
            'edit_item'             => "Редактировать",
            'update_item'           => "Обновить",
            'view_item'             => "Просмотр",
            'view_items'            => "Просмотр",
            'search_items'          => "Поиск",
            'not_found'             => "Не найдено",
            'not_found_in_trash'    => "Не найдено",
            'featured_image'        => "Изображение",
            'set_featured_image'    => "Установить изображение",
            'remove_featured_image' => "Удалить изображение",
            'use_featured_image'    => "Использовать изображение",
            'insert_into_item'      => "Вставить термин",
            'uploaded_to_this_item' => "Загрузить",
            'items_list'            => "Список лендингов",
            'items_list_navigation' => "Навигация по списку",
            'filter_items_list'     => "Фильтр списка",
        );

        $pt_args = array(
            'label'                 => 'Лендинг',
            'labels'                => $pt_labels,
            'supports'              => array( 'title', 'excerpt', 'editor', 'revisions', 'thumbnail'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-layout',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rewrite'               => array('slug'=> 'action', 'with_front'=> false)
        );

        register_post_type( $this->get_setting('post_type'), $pt_args );
    }

    protected function flush_permalinks() {

        $done = (int)get_option( 'fcon_rewrite_rules_flused' );

        if( !$done || $done != 1 ){
            flush_rewrite_rules(false);
            update_option( 'fcon_rewrite_rules_flused', 1 );
        }
        
    }

    protected function load_textdomain() {

        $domain = $this->get_setting('textdomain');
        $locale = get_locale();
        $mofile = $domain . '-' . $locale . '.mo';

        // Try to load from the languages directory first.
        if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) ) {
            return true;
        }

        // Load from plugin lang folder.
        $path = $this->get_setting('path') . 'lang/' . $mofile;
        
        return load_textdomain( $domain, $path );
    }

    public function load_landing_template( $template ) {

        if ( is_singular( fcon_get_setting('post_type') ) ) {

            $template = fcon_get_path( 'templates/landing.php');
        }

        return $template;
    }

    public function yandex_return_url( $payment_data, $pm_id, $donation_id ) {

        if( !class_exists('Leyka_Donations') ) {
            return $payment_data;
        }

        if( isset( $payment_data['confirmation'] ) && isset( $payment_data['confirmation']['return_url'] ) ) {

            $donation = Leyka_Donations::get_instance()->get_donation( $donation_id );

            $campaign = get_post($donation->campaign_id);

            if( $campaign->post_parent > 0 ) {
                $landing = new Fcon_Landing( $campaign->post_parent );
                $payment_data['confirmation']['return_url'] = $landing->get_success_url();
            }
        }

        return $payment_data;
    }
    

    public function add_metatags() {

        if ( ! is_singular( fcon_get_setting('post_type') ) ) {
            return;
        }

        $qo = get_queried_object();

        fcon_yandex_metatags( $qo );
    }

    
    // redirect
    public function campaign_to_landing_redirect() {

        $qo = get_queried_object(); 

        if( !is_a( $qo, 'WP_Post' ) ) {
            return;
        }

        $leyka_post_type = fcon_get_setting( 'leyka_campaign_post_type' );

        if ( $qo->post_type != $leyka_post_type ) {
            return;
        }

        if( $qo->post_parent == 0 ) {
            return;
        }

        $redirect = get_permalink( (int)$qo->post_parent );

        if( $redirect ) {
            wp_redirect($redirect, 301);
            die();
        }
    }

    public function correct_donor_news_status( $donation_meta_fields, $donation_id, $params ) {

        // store donor_subscribed value on all supported formats
        if( isset( $params['donor_subscribed'] ) && (int)$params['donor_subscribed'] === 1 ) {

            $donation_meta_fields['leyka_donor_subscribed'] = 1;
        }

        return $donation_meta_fields;
    }

} // class 
