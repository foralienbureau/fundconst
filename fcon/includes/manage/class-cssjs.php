<?php if( !defined('WPINC') ) die;
/**
 * CSS/JS loading and related functions
 * */

class Fcon_Cssjs {

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

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ), 35 );

        add_action( 'wp_enqueue_scripts', array( $this, 'front_whitelist' ), 40 );

        add_action( 'wp_print_styles', array( $this, 'print_early_inlines' ), 5 );

        add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin' ), 35 );
        
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor' ), 35 );

        add_filter( 'script_loader_tag', array( $this,  'add_defer_attribute' ), 10, 2 );

        add_action( 'wp_head', array( $this, 'add_favicon'), 5);

        add_action('wp_enqueue_scripts', array($this, 'leyka_fix_scripts'), 50);
    }


    public function leyka_fix_scripts() {

        // remove leykas css/js   
        wp_dequeue_script( 'leyka-easy-modal' );
        wp_dequeue_script( 'leyka-modal' );
        wp_dequeue_script( 'leyka-public' );
        wp_dequeue_script( 'leyka-revo-public' );
        
        wp_dequeue_style( 'leyka-revo-plugin-styles' );
        wp_dequeue_style( 'leyka-plugin-styles' );

        // support for CP - TODO: check for option
        if( !wp_script_is( 'leyka-cp-widget', 'enqueued' ) ) {

            wp_enqueue_script(
                'leyka-cp-widget', 
                'https://widget.cloudpayments.ru/bundles/cloudpayments', 
                array(), false, true
            );
        }
    }


    // revisions 
    protected function get_manifest() {

        if( null === $this->manifest ) {
            $manifest_path = fcon_get_path('assets/rev/rev-manifest.json'); 

            if ( file_exists($manifest_path) ) {
                $this->manifest = json_decode(file_get_contents($manifest_path), TRUE);
            } else {
                $this->manifest = array();
            }
        }

        return $this->manifest; 
    }

    protected function get_rev_filename( $filename ) {

        $manifest = $this->get_manifest();

        if ( array_key_exists($filename, $manifest) ) {
            return $manifest[$filename];
        }

        return $filename;
    }


    // whitelist
    public function front_whitelist() {
        global $wp_styles;

        if( !fcon_is_landing() ) {
            return;
        }

        $allowed = array(
            'admin-bar',
            'wp-block-library',
            'wp-block-library-theme',
            'global-styles',
            'fcon-design',
            'query-monitor',
            'fcon-theme-support',
        );

        $allowed = apply_filters( 'fcon_allowed_styles', $allowed );

        foreach( $wp_styles->queue as $style_id ) {

            if( !in_array( $style_id, $allowed ) ) {

                wp_dequeue_style( $style_id );
            }
        }
    }


    // front 
    public function enqueue_front() {

        if( !fcon_is_landing() ) {
            return;
        }

        $base_url = fcon_assets_url('rev/');


        // fonts 
        $fonts  = $this->prepare_fonts();
        $colors = $this->prepare_colors();

        if ( $fonts['inline_before'] ) {
            $this->set_early_inline_styles( $fonts['inline_before'] );
        }


        // styles
        wp_enqueue_style(
            'fcon-design',
            $base_url . $this->get_rev_filename('bundle.css'),
            $fonts['dependencies'] ? $fonts['dependencies'] : array(),
            null
        );


        // inline styles 
        $variables   = array_merge( $fonts['inline_after'], (array)$colors['inline_after'] );
        $custom_css  = sprintf( ":root {\n%s\n}\n", implode( "\n", $variables) );
        $custom_css .= $this->build_landing_css(); // append landing custom styles

        wp_add_inline_style( 'fcon-design', trim( $custom_css ) );


        // scripts
        wp_enqueue_script(
            'alpine',
            $base_url . $this->get_rev_filename('alpine.js'),
            array(),
            null,
            true
        );

        wp_enqueue_script(
            'fcon-front',
            $base_url . $this->get_rev_filename('bundle.js'),
            array( 'alpine' ),
            null,
            true
        );
    }

    protected function prepare_fonts() {

        $fonts_settings = $this->get_font_settings();

        $dependencies   = array();
        $inline_before  = array();
        $inline_after   = array();

        
        // title
        if ( $fonts_settings['title']['load_type'] == 'external' ) { 
            // enque external style sheet
            
            if ( !empty( $fonts_settings['title']['style_link'] ) ) {

                wp_enqueue_style(
                    'fcon-title-font',
                    trim( $fonts_settings['title']['style_link'] ),
                    false 
                );  

                $dependencies[] = 'fcon-title-font';
            }
               
        }
        else { 
            // add inline css 

            if ( !empty( $fonts_settings['title']['font_face'] ) ) {
                $inline_before[] = trim( $fonts_settings['title']['font_face'] );
            }
        }

        $inline_after[] = $this->get_font_family_code( 'title', $fonts_settings['title']['font_family'] );

        // text
        if ( $fonts_settings['body']['load_type'] == 'external' ) {
            // enque external style sheet
            
            if ( !empty( $fonts_settings['body']['style_link'] ) ) {

                wp_enqueue_style(
                    'fcon-body-font',
                    trim( $fonts_settings['body']['style_link'] ),
                    false 
                );  

                $dependencies[] = 'fcon-body-font';
            }
        }
        else {
            // add inline css 
            if ( !empty( $fonts_settings['title']['body'] ) ) {
                $inline_before[] = trim( $fonts_settings['body']['font_face'] );
            }
        }

        $inline_after[] = $this->get_font_family_code('body', $fonts_settings['body']['font_family']);


        return array(
            'dependencies'  => $dependencies,
            'inline_before' => $inline_before,
            'inline_after'  => $inline_after
        );
    }

    protected function get_font_settings() {

        $settings = array(
            'title' => array(
                'load_type'     => '',
                'style_link'    => '',
                'font_face'     => '',
                'font_family'   => ''
            ),
            'body' => array(
                'load_type'     => '',
                'style_link'    => '',
                'font_face'     => '',
                'font_family'   => ''
            ),
        );

        if( !function_exists( 'get_field' ) ) {
            return $settings;
        }

        if( !fcon_is_landing() ) {
            return $settings;
        }

        $qo_id = get_queried_object_id();

        // title 
        $title = get_field( 'title_font', $qo_id, true );

        if ( empty( $title['load_type'] ) || $title['load_type'] == 'none' ) {
            $title = get_field( 'title_font', 'options' );
        }
     
        $settings['title'] = wp_parse_args( $title, $settings['title'] );
        
        // body 
        $title = get_field( 'body_font', $qo_id, true );

        if ( !isset($title['load_type']) || $title['load_type'] == 'none' ) {
            $title = get_field( 'body_font', 'options' );
        }
        
        $settings['body'] = wp_parse_args( $title, $settings['body'] );

        return $settings;
    }

    protected function get_font_family_code( $type, $code ) {

        $code = trim( str_replace( array('font-family:', 'font-family', ':', ';'), '', $code ) );

        if ( !$code ) {
            $code = $type == 'body' ? 'Helvetica Neue, Helvetica, sans-serif' : 'Georgia, serif';
        }

        return sprintf( "--fcon-font-{$type}: {$code};" );
    }

    protected function set_early_inline_styles( $items ) {

        $this->early_inline_styles = array_merge(
            $this->early_inline_styles,
            (array)$items
        );
    }

    public function print_early_inlines() {

        if( empty($this->early_inline_styles) ) {
            return;
        }

        $output = implode( "\n", $this->early_inline_styles );

        // TODO. minify ???
        printf(
            "<style id='%s-inline-css'>\n%s\n</style>\n",
            'fcon-early',
            $output
        );
    }
    
    protected function prepare_colors() {

        if( !function_exists('get_field') ) {
            return '';
        }

        $colors = get_field( 'colors', 'options' );

        $body_bg = $this->hex_to_rgb( $colors['body_bg'] );
        $body_bg_rgb = sprintf(
            "%s, %s, %s", 
            $body_bg['red'], 
            $body_bg['green'],
            $body_bg['blue']
        );

        $body_text = $this->hex_to_rgb( $colors['body_text'] );
        $body_text_rgb = sprintf(
            "%s, %s, %s", 
            $body_text['red'], 
            $body_text['green'],
            $body_text['blue']
        );

        $basic_button = $this->hex_to_rgb( $colors['basic_button'] );
        $basic_button_rgb = sprintf(
            "%s, %s, %s", 
            $basic_button['red'], 
            $basic_button['green'],
            $basic_button['blue']
        );

        $inline_after ="
            --fcon-color-body-bg: {$colors['body_bg']};
            --fcon-color-body-bg-rgb: {$body_bg_rgb};
            --fcon-color-body-text: {$colors['body_text']};
            --fcon-color-body-text-rgb: {$body_text_rgb};
            --fcon-color-link-decoration: {$colors['link_decoration']};
            --fcon-color-basic-button: {$colors['basic_button']};
            --fcon-color-basic-button-rgb: {$basic_button_rgb};
            --fcon-color-progress-bar: {$colors['progress_bar']};
        ";

        return array(
            'inline_after'  => $inline_after
        );
    }

    protected function hex_to_rgb( $colour ) {

        if ( $colour[0] == '#' ) {
                $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
                return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );

        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }

    protected function build_landing_css() {
        
        if ( !fcon_is_landing() || !function_exists( 'get_field' ) ) {
            return '';
        }

        $css = get_field( 'custom_css_code', 'options' );

        $qo_id = get_queried_object_id();

        $css .= get_field( 'custom_css_code', $qo_id, true);

        return trim( $css );
    }



    // util
    public function enqueue_admin() {

        $url = fcon_assets_url('rev/');

        wp_enqueue_style(
            'fcon-admin',
            $url . $this->get_rev_filename('admin.css'), 
            array(), 
            null
        );
    }

    public function enqueue_editor() {

        $screen = get_current_screen();

        if ( $screen && $screen->post_type == 'fconland' ) {

            remove_editor_styles();

            add_theme_support( 'editor-styles' );

            $url = fcon_assets_url('rev/');

            wp_enqueue_style( 
                'fcon-editor', 
                $url . $this->get_rev_filename('editor.css'), 
                false, 
                null, 
                'all' 
            );
        }
    }

    public function add_defer_attribute( $tag, $handle ) {

        if( !fcon_is_landing() ) {
            return $tag;
        }

        $handles = array(
            'alpine',
        );

        foreach( $handles as $defer_script ) {
            if ( $defer_script === $handle ) {
                return str_replace( ' src', ' defer="defer" src', $tag );
            }
        }

        return $tag;
    }

    public function add_favicon() {

        if( !fcon_is_landing() ) {
            return;
        }
        
        $favicon_id = get_option( 'options_favicon' );

        if( !$favicon_id ) {
            return;
        }

        $png = wp_get_attachment_url( $favicon_id );

        if( !$png ) {
            return;
        }
            
        echo "<link href='{$png}' rel='icon' type='image/png' sizes='32x32'>";
    }


} // class 
