<?php   /* Thank you template */

if( !function_exists('get_field') ) {
    return;
}

if ( !function_exists('fcon_thankyou_widget') ) {
    function fcon_thankyou_widget( $text, $button ) {

        $b_text = isset( $button['text'] ) ? trim( $button['text'] ) : '';
        $b_link = isset( $button['link'] ) ? trim( $button['link'] ) : '';
        $b_color = isset( $button['color'] ) ? trim( $button['color'] ) : '';

        if( !$text && !$b_link ) {
            return;
        }

        $type = $text ? 'filled' : 'transparent';
    ?>
    <div class="thankyou-action thankyou-action--<?php echo esc_attr( $type ); ?>">
        <?php if( $text) { ?>
            <div class="thankyou-action__text">
                <?php echo apply_filters('the_content', $text ); ?>
            </div>
        <?php } ?>
        <?php if( $b_link && $b_text ) { ?>
            <div class="thankyou-action__button">
                <a
                    <?php if( $b_color ) { ?>style="background-color: <?php echo esc_attr($b_color);?>;"<?php }?>
                    href="<?php echo esc_url( $b_link );?>"
                    class="wpbutton filled"><span><?php echo esc_html( $b_text );?></span></a>
            </div>
        <?php }?>
    </div>
    <?php 
    }
}


// base
$prefix = "thankyou_section";


// fields
$title      = get_field("{$prefix}_title");
$lead       = get_field("{$prefix}_lead");
$widget     = get_field("{$prefix}_widget");
$file       = get_field("{$prefix}_file");
$layout     = get_field("{$prefix}_layout");


// images
$cover          = get_field("{$prefix}_cover");
$cover_desktop  = $cover['desktop'] ? wp_get_attachment_image_url((int)$cover['desktop'], 'full') : '';
$cover_mobile   = $cover['mobile'] ? wp_get_attachment_image_url((int)$cover['mobile'], 'full'): '';
$cover_id       = uniqid('cover-');

if( empty( $cover_mobile ) ) {
    $cover_mobile = $cover_desktop;
}

$widget_type = $widget['text'] ? 'filled' : 'transparent';

$base_css = array(
    $prefix,
    'widget-' . $widget_type,
    'mobile-' . $layout['mobile'],
    'desktop-' . $layout['desktop']
);

?>
<div class="<?php echo esc_attr( implode(' ', $base_css) );?>">
    <style>
    #<?php echo $cover_id;?> {
        background-color: <?php echo $cover['color'];?> ;
    }
    @media screen and (max-width: 639px) {
        #<?php echo $cover_id;?> {
            background-image:  url(<?php echo esc_url( $cover_mobile ); ?>);
        }
        .thankyou_section__title {
            font-size: <?php echo esc_attr($title['size_mobile']);?>px;
        }
    }
    @media screen and (min-width: 640px) {
        #<?php echo $cover_id;?> {
            background-image:  url(<?php echo esc_url( $cover_desktop ) ;?>);
        }
        .thankyou_section__title {
            font-size: <?php echo esc_attr($title['size_desktop']);?>px;
        }
    }
    </style>

    <div id="<?php echo esc_attr( $cover_id );?>" class="<?php echo esc_attr( $prefix );?>__bg"></div>
    
    <div class="<?php echo esc_attr($prefix);?>__content"><div class="<?php echo esc_attr($prefix);?>__grid">

            <div class="<?php echo esc_attr($prefix);?>__header">
                <?php if( $title['text'] ) { ?>
                    <div
                        class="<?php echo esc_attr($prefix);?>__title"
                        style="color: <?php echo $title['color'];?>;">
                        <?php echo apply_filters( 'the_title', $title['text']);?>
                    </div>
                <?php } ?>
                <?php if( $lead['text'] ) { ?>
                    <div
                        class="<?php echo esc_attr($prefix);?>__lead <?php echo esc_attr($lead['format']);?>"
                        style="color: <?php echo esc_attr($lead['color']);?>;">
                        <?php echo apply_filters( 'the_content', $lead['text']);?>
                    </div>
                <?php } ?>

                <?php if( $file['file'] && $file['text'] ) { ?>
                    <div class="<?php echo esc_attr($prefix);?>__file">
                        <a
                            download
                            target="_blank"
                            style="color: <?php echo esc_attr( $file['color'] );?>;"
                            class="<?php echo esc_attr($prefix);?>__file-link"
                            href="<?php echo esc_url( $file['file'] ); ?>"><?php echo esc_html( $file['text'] ); ?></a>
                    </div>
                <?php } ?>
            </div>

            <?php if( $widget['text'] || $widget['button']['link'] ) { ?>
                <div class="<?php echo esc_attr($prefix);?>__widget">
                    <?php fcon_thankyou_widget( $widget['text'], $widget['button'] ); ?>
                </div>
            <?php } ?>

    </div></div>

</div><!-- wrapper -->
