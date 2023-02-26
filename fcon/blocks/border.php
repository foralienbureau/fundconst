<?php if( !defined('WPINC') ) die;
/**
 * Block Name: Призыв к действию
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

if( !function_exists('get_field') ) {
    return;
}


// base
$prefix = 'border';
$base = new Fcon_Blockbase($prefix, $block, $post_id);


// fields
$height      = get_field("{$prefix}_height");
$color      = get_field("{$prefix}_color");
$has_margin = get_field("{$prefix}_has_margin");

if( !$height ) {
    $height = 1;
}

if( !$color ) {
    $color = '#DBDBDB';
}

// commons
$custom  = array();

if( !$has_margin ) {
    $custom[] = 'no-margin';
}

$classes = $base->get_css_classes_string( $custom );
$id = $base->get_id_string();
?>
<div <?php if( $id ) {?>id="<?php echo $id; ?>"<?php } ?> class="<?php echo esc_attr( $classes );?>">
    <div class="custom-border" style="height:<?php echo esc_attr($height);?>px; background-color:<?php echo $color;?>"></div>
</div>
