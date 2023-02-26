<?php

$footer_logo = get_option('options_logo_in_footer');

$footer_description = get_post_meta( get_queried_object_id(), 'footer_text', true);

$oferta = get_option('options_oferta_page');

$privacy = get_option('options_privacy_page');
?>
</div><!-- /site-content -->

<footer class="fcon-footer fcon-container">
    <div class="fcon-footer__branding">
        <a href="<?php home_url('/');?>" class="fcon-logo-link">
            <?php echo wp_get_attachment_image((int)$footer_logo, 'full'); ?>
        </a>
    </div>
    <div class="fcon-footer__description">
        <div class="fcon-footer__description-text">
            <?php echo apply_filters( 'the_content', $footer_description ); ?>
        </div>
    </div>
    <div class="fcon-footer__pages">
        <?php if( $oferta ) { ?>
            <div class="fcon-footer__pages-link">
                <a href="<?php echo get_permalink( $oferta );?>" class="footer-link"><?php _e('Donations oferta', 'fcon');?></a>
            </div>
        <?php }?>
        <?php if( $privacy ) { ?>
            <div class="fcon-footer__pages-link">
                <a href="<?php echo get_permalink( $privacy );?>" class="footer-link"><?php _e('Privacy policy', 'fcon');?></a>
            </div>
        <?php }?>
    </div>
</footer>

</div><!-- /the-site -->

<?php wp_footer();  ?>
</body>
</html>
