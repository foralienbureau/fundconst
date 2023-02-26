<?php if( !defined('WPINC') ) die;
// functions

function fcon_log( $message ) {

    $message = is_string( $message ) ? $message : print_r( $message, true );

    if ( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
        error_log($message);
    }
}

function fcon_get_path( $path ) {

    $base = fcon()->get_setting( 'path' );

    return $base . $path;
}

function fcon_get_block_path( $blockfile ) {

    $base = fcon()->get_setting( 'path' );

    return $base . 'blocks/' . $blockfile;
}

function fcon_assets_url( $path ) {

    $base = fcon()->get_setting('url');

    return $base . 'assets/' . $path;
}

function fcon_get_setting( $key ) {

    $try = fcon()->get_text_setting( $key);

    if( $try === null) {
        $try = fcon()->get_setting( $key );
    }
    
    return $try;
}

function fcon_load_template_part( $filename ) {

    $path = fcon()->get_setting( 'path' );

    include $path . "templates/" . $filename;
}

function fcon_is_external( $link ) {

    if ( 0 === strpos( $link, '#') ) {
        // local fragment
        return false;
    }

    $home = home_url('');
    $home = str_replace(array('http://', 'https://'), '', $home);
    
    if( false === strpos( $link, $home) ) {
        return true;
    }

    return false;
}

function fcon_close_icon() {
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 18">
  <path d="M1069.15344,193.082031 L1084.63007,208.558655 C1084.77313,208.701721 1084.94287,208.797394 1085.13928,208.845673 C1085.33569,208.893951 1085.53049,208.893402 1085.72366,208.844025 C1085.91684,208.794647 1086.08893,208.697998 1086.23993,208.554077 C1086.38373,208.403198 1086.48032,208.22937 1086.52969,208.032593 C1086.57907,207.835815 1086.57947,207.640991 1086.53088,207.44812 C1086.4823,207.255249 1086.38684,207.087646 1086.24451,206.945313 L1070.7666,191.46759 C1070.61877,191.326721 1070.44843,191.231995 1070.25555,191.183411 C1070.06268,191.134827 1069.86749,191.133057 1069.66998,191.178101 C1069.47247,191.223145 1069.30176,191.321167 1069.15784,191.472168 C1069.01404,191.623047 1068.91782,191.795471 1068.86917,191.989441 C1068.82053,192.183411 1068.82053,192.378601 1068.86917,192.575012 C1068.91782,192.771423 1069.01257,192.94043 1069.15344,193.082031 Z M1069.15344,206.946411 C1069.01257,207.08728 1068.91605,207.256104 1068.86386,207.452881 C1068.81168,207.649658 1068.80991,207.846802 1068.85855,208.044312 C1068.9072,208.241821 1069.00696,208.412109 1069.15784,208.555176 C1069.30176,208.699097 1069.47247,208.795563 1069.66998,208.844574 C1069.86749,208.893585 1070.06268,208.893585 1070.25555,208.844574 C1070.44843,208.795563 1070.61877,208.700623 1070.7666,208.559753 L1086.24451,193.08313 C1086.38684,192.940063 1086.4823,192.770325 1086.53088,192.573914 C1086.57947,192.377502 1086.57907,192.180908 1086.52969,191.984131 C1086.48032,191.787354 1086.38373,191.617065 1086.23993,191.473267 C1086.08893,191.329346 1085.91507,191.232697 1085.71835,191.183319 C1085.52164,191.133942 1085.32684,191.133575 1085.13397,191.18222 C1084.9411,191.230865 1084.77313,191.326355 1084.63007,191.468689 L1069.15344,206.946411 Z" transform="translate(-1068 -191)"/>
</svg>
<?php
}

function fcon_is_landing() {

    if( is_admin() ){
        return false;
    }

    $post_type = fcon_get_setting( 'post_type' );

    return (bool)is_singular( $post_type );
}


function fcon_is_thankyou_mode() {

    $post_type = fcon_get_setting( 'post_type' );

    if ( !is_singular( $post_type ) ) {
        return false;
    }

    if( isset( $_GET['thankyou'] ) && (int)$_GET['thankyou'] === 1 ) {
        return true;
    }

    return false;
}


function fcon_yandex_metatags( $qo ) {

    $type = $qo->post_type.'-'.$qo->ID;
    $date = get_post_time('c', false, $qo->ID );
    $creator = get_the_author_meta('display_name', $qo->post_author);
?>
<meta itemprop="identifier" content="<?php echo esc_attr( $type );?>">
<meta itemprop="dateModified" content="<?php echo esc_attr( $date );?>" />
<meta itemprop="author" content="Редактор: <?php echo esc_attr( $creator );?>" />
<?php 
}
