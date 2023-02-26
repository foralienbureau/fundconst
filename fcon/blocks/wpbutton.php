<?php if( !defined('WPINC') ) die;
/**
 * Block Name: Кнопка
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
$prefix = 'wpbutton';
$base = new Fcon_Blockbase($prefix, $block, $post_id);


// fields
$text     = get_field("{$prefix}_text");
$url      = get_field("{$prefix}_link");
$format   = get_field("{$prefix}_format");

if( !$url ) {
    return;
}

$target = fcon_is_external( $url ) ? ' target="_blank" rel="noopener"' : '';


// commons
$classes = $base->get_css_classes_string();
$id = $base->get_id_string();

?>
<div <?php if( $id ) {?>id="<?php echo $id; ?>"<?php } ?> class="<?php echo esc_attr( $classes );?>">
    <a
        class="<?php echo esc_attr( "{$prefix} {$format}" );?>"
        href="<?php echo esc_url( $url );?>"
        <?php echo $target;?>><span><?php echo esc_html( $text );?></span></a>
</div>
