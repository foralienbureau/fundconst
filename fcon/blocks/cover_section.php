<?php if( !defined('WPINC') ) die;
/**
 * Block Name: Секция обложки
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

if( !function_exists('get_field') ) {
    return;
}

if ( !function_exists('fcon_closed_widget') ) {
    function fcon_closed_widget( $text, $button ) {

        if( !$text ) {
            return;
        }

        $b_text = isset( $button['text'] ) ? trim( $button['text'] ) : '';
        $b_link = isset( $button['link'] ) ? trim( $button['link'] ) : '';
    ?>
        <div class="thankyou-widget">
            <div class="thankyou-widget__text">
                <?php echo apply_filters('the_content', $text ); ?>
            </div>
            <?php if( $b_link && $b_text ) { ?>
                <div class="thankyou-widget__button">
                    <a href="<?php echo esc_url( $b_link );?>" class="wpbutton filled"><span><?php echo esc_html( $b_text );?></span></a>
                </div>
            <?php }?>
        </div>
    <?php
    }
}

if ( !function_exists('fcon_notice_widget') ) {

    function fcon_notice_widget() {
    ?>
        <div class="notice-widget">
            <div class="notice-widget__text">
                <?php echo esc_html( fcon_get_setting( 'error_no_campaign' ) ); ?>
            </div>
        </div>
    <?php
    }
}

// base
$prefix = 'cover_section';
$base = new Fcon_Blockbase($prefix, $block, $post_id);


// fields
$title      = get_field("{$prefix}_title");
$lead       = get_field("{$prefix}_lead");


// images
$cover          = get_field("{$prefix}_cover");
$cover_desktop  = $cover['desktop'] ? wp_get_attachment_image_url((int)$cover['desktop'], 'full') : '';
$cover_mobile   = $cover['mobile'] ? wp_get_attachment_image_url((int)$cover['mobile'], 'full'): '';
$cover_id       = uniqid('cover-');


if( empty($cover_mobile) ) {
    $cover_mobile = $cover_desktop;
}

// display settings - TODO: use them in template
$widget = get_field("{$prefix}_widget");
$layout = get_field("{$prefix}_layout");


// landing
$landing = new Fcon_Landing( $post_id );


// commons
$has_target = $landing->has_target();

if ( $has_target ) {
    $specific = array('has-target');
}
else {
    $specific = array('has-no-target');
}

if( !$landing->is_open() ) {
    $specific[] = 'state-closed';
    $closed = get_field("{$prefix}_widget_close");
}
else if( !$landing->has_connected_campaign() ) {
    $specific[] = 'state-error';
    $closed = null;
}
else {
    $specific[] = 'state-open';
    $closed = null;
}

$classes    = $base->get_css_classes_string( $specific  );
$id         = $base->get_id_string();

$base_css   = array($prefix, 'mobile-'.$layout['mobile'], 'desktop-'.$layout['desktop']);
?>
<div <?php if( $id ) {?>id="<?php echo $id; ?>"<?php } ?> class="<?php echo esc_attr( $classes );?>">

    <div class="<?php echo esc_attr( implode(' ', $base_css) );?>">
        <style>
            #<?php echo $cover_id;?> {
                background-color: <?php echo $cover['color'];?> ;
            }
            @media screen and (max-width: 639px) {
                #<?php echo $cover_id;?> {
                    background-image:  url(<?php echo esc_url($cover_mobile); ?>);
                }

                h1.cover_section__title  {
                    font-size: <?php echo esc_attr($title['size_mobile']);?>px;
                }
            }
            @media screen and (min-width: 640px) {
                #<?php echo $cover_id;?> {
                    background-image:  url(<?php echo esc_url($cover_desktop) ;?>);
                }

                h1.cover_section__title {
                    font-size: <?php echo esc_attr($title['size_desktop']);?>px;
                }
            }
        </style>

        <div id="<?php echo esc_attr($cover_id);?>" class="<?php echo esc_attr($prefix);?>__bg"></div>

        <div class="<?php echo esc_attr($prefix);?>__content"><div class="<?php echo esc_attr($prefix);?>__grid">

            <div class="<?php echo esc_attr($prefix);?>__header">
                <?php if( $title['text'] ) { ?>
                    <h1
                        itemprop="headline"
                        class="<?php echo esc_attr($prefix);?>__title"
                        style="color: <?php echo $title['color'];?>;">
                        <?php echo apply_filters( 'the_title', $title['text']);?>
                    </h1>
                <?php } ?>
                <?php if( $lead['text'] ) { ?>
                    <div
                        class="<?php echo esc_attr($prefix);?>__lead <?php echo esc_attr($lead['format']);?>"
                        style="color: <?php echo $lead['color'];?>;">
                        <?php echo apply_filters( 'the_title', $lead['text']);?>
                    </div>
                <?php } ?>
            </div>

            <div class="<?php echo esc_attr($prefix);?>__widget"><?php  

                ob_start(); // prepare widget output 
                
                if( !$landing->is_open() ) { // closed state 

                    fcon_closed_widget( $closed['text'], $closed['button'] ); 
                
                } else if ( !$landing->has_connected_campaign() ) { 
                    
                    fcon_notice_widget();
                
                } else { ?>
                    <div class="compact-widget <?php if( $has_target ) { echo 'has-target'; } ?>">
                        <?php if( $has_target ) { ?>
                            <div class="compact-widget__target">
                                <?php
                                    $target = new Fcon_Target_Template( $landing );
                                    $target->print_markup();
                                ?>
                            </div>
                        <?php }?>
                        <div class="compact-widget__form">
                            <?php
                                $args = array();

                                if ( isset( $widget['title'] ) && !empty( $widget['title'] ) ) {
                                    $args['title_text'] = trim( $widget['title'] );
                                    $args['show_title'] = true;
                                }
                                else {
                                    $args['show_title'] = true;
                                    $args['title_text'] = __('Select donation amount', 'fcon');
                                }

                                if ( isset( $widget['help_button_text'] ) && !empty( $widget['help_button_text'] ) ){
                                    $args['button_text'] = trim($widget['help_button_text']);
                                }
                                else {
                                    $args['button_text'] = __( 'Support', 'fcon' );
                                }
                                
                                $form = new Fcon_Donation_Form( $post_id, 'compact', $args ); 
                                $form->display();
                            ?>
                        </div>
                    </div>
                <?php } 
                    $widget = ob_get_contents();
                    ob_end_clean();

                    echo trim($widget);
                ?></div>
        </div></div>

    </div><!-- cover_section -->

    <?php if( !$landing->is_open() ) { // closed state ?>
        <div class="<?php echo esc_attr($prefix);?>__closed_mobile">
            <?php  fcon_closed_widget( $closed['text'], $closed['button'] ); ?>
        </div>
    <?php } else if( !$landing->has_connected_campaign() ) { ?>
        <div class="<?php echo esc_attr($prefix);?>__closed_mobile">
            <?php  fcon_notice_widget(); ?>
        </div>
    <?php } ?>

    <!-- modal -->
    <?php if( $landing->is_open() ) { ?>
        <script id="fconModal">
            function fconModal() {
                return {
                    open: false,

                    closeModal: function( event, $dispatch ) {

                        this.open = false;

                        $dispatch('body-unlock');
                    },

                    openModal: function( event, $dispatch ) {

                        this.open = true;

                        this.amount = event.detail.amount;

                        $dispatch('body-lock');
                    }
                }
            }
        </script>
        <div
            x-data="fconModal"
            @modal-open.window="openModal($event, $dispatch)"
            x-show="open"
            :class="{'is-open': open}"
            class="fcon-modal">
            <div
                @click.outside="closeModal($event, $dispatch)"
                class="fcon-modal__frame">
                <div class="fcon-modal__close">
                    <a
                        @click.prevent="closeModal($event, $dispatch)"
                        href="#" class="fcon-modal__close-link">
                        <?php fcon_close_icon();?>
                    </a>
                </div>
                <div class="fcon-modal__content">
                    <?php
                        $modal = get_field("{$prefix}_modal");

                        $modal_args = array( 'show_title' => true );

                        if ( isset( $modal['title'] ) && !empty( $modal['title'] ) ) {
                            $modal_args['title_text'] = trim( $modal['title'] );
                        }
                        else {
                            $modal_args['title_text'] = __('Make donation', 'fcon');
                        }

                        if ( isset( $modal['help_button_text'] ) && !empty( $modal['help_button_text'] ) ){
                            $modal_args['button_text'] = trim($modal['help_button_text']);
                        }
                        else {
                            $modal_args['button_text'] = __( 'Donate', 'fcon' );
                        }

                        $form = new Fcon_Donation_Form( $post_id, 'full', $modal_args ); 
                        $form->display();
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
