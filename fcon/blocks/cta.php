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
$prefix = 'cta';
$base = new Fcon_Blockbase($prefix, $block, $post_id);


// fields
$title      = get_field("{$prefix}_title");
$text       = get_field("{$prefix}_text");
$link       = get_field("{$prefix}_link"); 
$target     = $link && fcon_is_external( $link['url'] ) ? ' target="_blank" rel="noopener"' : '';


// styling
$align = get_field("{$prefix}_align");
$color = get_field("{$prefix}_color");

if ( !$align) {
    $align = 'left';
}


// images
$cover          = get_field("{$prefix}_cover");
$cover_desktop  = $cover['desktop'] ? wp_get_attachment_image_url((int)$cover['desktop'], 'full') : '';
$cover_mobile   = $cover['mobile'] ? wp_get_attachment_image_url((int)$cover['mobile'], 'full'): '';
$css_id         = uniqid('cta-');


// commons
$classes = $base->get_css_classes_string();
$id = $base->get_id_string();

$base_css = array($prefix, 'align-'.$align);
?>
<div <?php if( $id ) {?>id="<?php echo $id; ?>"<?php } ?> class="<?php echo esc_attr( $classes );?>">
    <style>
        #<?php echo $css_id;?> .fcon-cta-bg {
            background-color: <?php echo $cover['color'];?> ;
        }
        <?php if( $color['title'] ) { ?>
            #<?php echo $css_id;?> .fcon-cta-title {
                color: <?php echo $color['title'];?> ;
            }
        <?php } ?>
        <?php if( $color['text'] ) { ?>
            #<?php echo $css_id;?> .fcon-cta-text {
                color: <?php echo $color['text'];?> ;
            }
        <?php } ?>
        @media screen and (max-width: 639px) {
            #<?php echo $css_id;?> .fcon-cta-bg {
                background-image:  url(<?php echo esc_url($cover_mobile); ?>);
            }
        }
        @media screen and (min-width: 640px) {
            #<?php echo $css_id;?> .fcon-cta-bg {
                background-image:  url(<?php echo esc_url($cover_desktop) ;?>);
            }
        }
    </style>

    <div id="<?php echo esc_attr($css_id);?>" class="<?php echo esc_attr( implode(' ', $base_css) );?>">
        <div class="<?php echo esc_attr($prefix);?>__bg fcon-cta-bg"></div>

        <div class="<?php echo esc_attr($prefix);?>__content">
            <div class="<?php echo esc_attr($prefix);?>__grid">
                <?php if( $title ) { ?>
                    <div class="<?php echo esc_attr($prefix);?>__title fcon-cta-title">
                        <?php echo apply_filters( 'the_title', $title);?>
                    </div>
                <?php } ?>

                <?php if( $text ) { ?>
                    <div class="<?php echo esc_attr($prefix);?>__text fcon-cta-text">
                        <?php echo apply_filters( 'the_content', $text);?>
                    </div>
                <?php } ?>

                <?php if( isset($link['url']) && $link['url'] ) { ?>
                    <div class="<?php echo esc_attr($prefix);?>__button">
                        <a
                            class="wpbutton <?php echo esc_attr( $link['format'] );?>"
                            href="<?php echo esc_url( $link['url'] );?>"
                            <?php echo $target;?>><span><?php echo esc_html( $link['text'] );?></span></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
