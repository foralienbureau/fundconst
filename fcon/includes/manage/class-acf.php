<?php if( !defined('WPINC') ) die;
/**
 * ACF compatibility functions, blocks and filters
 * */

class Fcon_Acf {

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

        add_filter( 'acf/settings/save_json', array( $this, 'json_save_point' ) );

        add_filter( 'acf/settings/load_json', array( $this, 'json_load_point' ) );

        add_action( 'init', array( $this, 'options_pages' ) );

        add_filter( 'block_categories_all', array( $this, 'add_block_category'), 10, 2 );

        add_action( 'acf/init', array( $this, 'register_blocks' ) );

        add_filter( 'acf/load_field_groups', array( $this, 'private_field_groups' ), 30 );
    }


    public function json_save_point( $path ) {

        if ( defined('FCON_ACF_SAVE_POINT') && FCON_ACF_SAVE_POINT ) {

            $path = fcon_get_path('acf-json/');
        }

        return $path;
    }

    public function json_load_point( $paths ) {

        $path = fcon_get_path('acf-json/');

        $paths[] = $path;

        return $paths;
    }

    public function options_pages() {

        if ( function_exists( 'acf_add_options_page' ) ) {

            $cap = fcon_get_setting('capability');

            $args = array(
                'page_title'        => 'Настройки конструктора лендингов',
                'menu_title'        => 'Конструктор',
                'menu_slug'         => 'fcon-basics',
                'capability'        => $cap,
                'position'          => false,
                'parent_slug'       => 'options-general.php',
                'icon_url'          => false,
                'redirect'          => false,
                'post_id'           => 'options',
                'autoload'          => false,
                'update_button'     => 'Сохранить настройки',
                'updated_message'   => 'Настройки сохранены',
            );

            acf_add_options_page( $args );
        }
    }


    function add_block_category( $block_categories, $block_editor_context ) {

        $post_type = fcon_get_setting( 'post_type' );
        $name = fcon_get_setting( 'name' );

        if( $block_editor_context->post->post_type == $post_type ) {
            $block_categories = array_merge(
                array(
                    array(
                        'slug'  => 'fcon-blocks',
                        'title' => $name,
                        'icon'  => 'layout' 
                    )
                )
            );
        }

        return $block_categories;
    }

    public function register_blocks() {

        $pt = fcon_get_setting('post_type');
        $foreground = '#F11112';

        acf_register_block_type(array(
            'name'              => 'wpbutton',
            'title'             => 'Кнопка',
            'description'       => 'Кнопка с предустановленными стилями',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'button'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     => false,
                'mode'      => true,
                'anchor'    => true
            ),
            'render_template' => fcon_get_block_path('wpbutton.php'),
        ));

        acf_register_block_type(array(
            'name'              => 'donation_section',
            'title'             => 'Форма пожертования',
            'description'       => 'Секция формы пожертвования с настройками',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'money'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     =>  array( 'left', 'center' ),
                'mode'      => true,
                'anchor'    => true,
                'color'     => array(
                    'background' => true,
                    'text'       => true
                )
            ),
            'render_template' => fcon_get_block_path('donation_section.php'),
        ));


        acf_register_block_type(array(
            'name'              => 'emtext',
            'title'             => 'Высказывание',
            'description'       => 'Крупный текст к декоративными отбивками',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'layout'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     => false,
                'mode'      => true,
                'anchor'    => true,
                'color'     => false
            ),
            'render_template' => fcon_get_block_path('emtext.php'),
        ));

        acf_register_block_type(array(
            'name'              => 'cta',
            'title'             => 'Призыв к действию',
            'description'       => 'Блок призыва к действию - с кнопкой',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'layout'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     =>  array( 'left', 'center' ),
                'mode'      => true,
                'anchor'    => true
            ),
            'render_template' => fcon_get_block_path('cta.php'),
        ));

        acf_register_block_type(array(
            'name'              => 'cover_section',
            'title'             => 'Секция обложки',
            'description'       => 'Стартовый экран с обложкой и платежным виджетом',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'layout'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     => false,
                'mode'      => true,
                'anchor'    => true
            ),
            'render_template' => fcon_get_block_path('cover_section.php'),
        ));


        acf_register_block_type(array(
            'name'              => 'border',
            'title'             => 'Линия',
            'description'       => 'Линия для отбивки элементов',
            'category'          => 'fcon-blocks',
            'post_types'        => array( $pt ),
            'mode'              => 'edit',
            'icon'              => array(
                'foreground' => $foreground,
                'src'        => 'layout'
            ),
            'mode' => 'edit',
            'supports'          => array(
                'align'     => true,
                'mode'      => true,
                'anchor'    => false
            ),
            'render_template' => fcon_get_block_path('border.php'),
        ));
    }


    
    public function private_field_groups( $field_groups ) {

        if ( defined('FCON_ACF_SAVE_POINT') && FCON_ACF_SAVE_POINT ) {
            // we are in dev mode - sync is possible
            return $field_groups;
        } 

        if ( !$field_groups || empty( $field_groups ) ) {
            return $field_groups;
        }

        $fcon_groups = $this->get_acf_json_field_groups_keys();

        foreach( $field_groups as $i => $group ) {

            if ( !isset( $group['key'] ) ) {
                continue;
            }

            if ( in_array( $group['key'], $fcon_groups ) ) {
                // fcon groups are private - for not to mix with theme's groups
                $field_groups[$i]['private'] = 1;
            }
        }

        return $field_groups;
    }

    protected function get_acf_json_field_groups_keys() {

        $keys   = array();
        $path   = fcon_get_path('acf-json/');
        $files  = scandir( $path );

        if ( $files ) {
            foreach ( $files as $filename ) {

                // Ignore hidden files.
                if ( $filename[0] === '.' ) {
                    continue;
                }

                // Ignore sub directories.
                $file = untrailingslashit( $path ) . '/' . $filename;
                if ( is_dir( $file ) ) {
                    continue;
                }

                // Ignore non JSON files.
                $ext = pathinfo( $filename, PATHINFO_EXTENSION );
                if ( $ext !== 'json' ) {
                    continue;
                }

                // Read JSON data.
                $json = json_decode( file_get_contents( $file ), true );
                if ( ! is_array( $json ) || ! isset( $json['key'] ) ) {
                    continue;
                }

                // Append data.
                $keys[] = $json['key'];
            }
        }

        return $keys;
    }

} // class 
