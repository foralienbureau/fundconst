<?php if( !defined('WPINC') ) die;
/**
 * Block Name: Форма пожертования
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

if( !function_exists('get_field') ) {
    return;
}

$landing = new Fcon_Landing( $post_id );

if( !$landing->is_open() || !$landing->has_connected_campaign() ) {
    // don't display when campaign is closed
    // or no connected layka campaign found
    return;
}

// base
$prefix = 'donation_section';
$base = new Fcon_Blockbase($prefix, $block, $post_id);


// fields
$title    = get_field( "{$prefix}_title" );
$button   = get_field( "{$prefix}_button" );
$css_id   = uniqid('dsec-');

// images
$cover          = get_field("{$prefix}_cover");
$cover_desktop  = $cover['desktop'] ? wp_get_attachment_image_url((int)$cover['desktop'], 'full') : '';
$cover_mobile   = $cover['mobile'] ? wp_get_attachment_image_url((int)$cover['mobile'], 'full'): '';
$cover_id       = uniqid('cover-');

if( empty($cover_mobile) ) {
    $cover_mobile = $cover_desktop;
}

// colors 
$custom_classes = array();

if( $cover['text_color'] ) {
    $custom_classes[] = 'has-text-color';
}

if( $cover['color'] ) {
    $custom_classes[] = 'has-background-color';
}

// commons
$classes = $base->get_css_classes_string( $custom_classes );
$id = $base->get_id_string();

?>
<div <?php if( $id ) {?>id="<?php echo $id; ?>"<?php } ?> class="<?php echo esc_attr( $classes );?>">
    <style>
        #<?php echo $css_id;?> {
            color: <?php echo $cover['text_color']; ?>;
        }
        #<?php echo $cover_id;?> {
            background-color: <?php echo $cover['color'];?> ;
        }
        @media screen and (max-width: 639px) {
            #<?php echo $cover_id;?> {
                background-image:  url(<?php echo esc_url($cover_mobile); ?>);
            }
        }
        @media screen and (min-width: 640px) {
            #<?php echo $cover_id;?> {
                background-image:  url(<?php echo esc_url($cover_desktop) ;?>);
            }
        }
    </style>

    <div id="<?php echo esc_attr($css_id);?>" class="<?php echo esc_attr( $prefix );?>">

        <div id="<?php echo esc_attr($cover_id);?>" class="<?php echo esc_attr($prefix);?>__bg"></div>

        <div class="<?php echo esc_attr($prefix);?>__title align-<?php echo esc_attr( $title['align'] );?>">
            <?php echo apply_filters( 'the_title', $title['text']);?>
        </div>

        <div class="<?php echo esc_attr($prefix);?>__widget">
            <?php
                $args = array(
                    'show_title'    => false,
                    'button_text'   => $button
                );
                $form = new Fcon_Donation_Form( $post_id, 'full', $args ); 
                $form->display();
            ?>
        </div>
    </div>
</div>
