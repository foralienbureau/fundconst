<?php
/** 
 * Basic landing template
 */

$classes = array( 'fcon-konstructor' );

if( fcon_is_thankyou_mode() ) {
    $classes[] = 'fcon-konstructor--thankyou';
}

fcon_load_template_part('header.php'); ?>
<div itemscope itemtype="http://schema.org/Article" class="<?php echo esc_attr( implode(' ', $classes) ); ?>">
<div  itemprop="articleBody" class="the-content">
    <?php
        if( fcon_is_thankyou_mode() ) {
            fcon_load_template_part('thankyou.php'); 
        }
        else if( have_posts() ) {
            the_post();
            the_content();
        }
    ?>
</div></div>
<?php fcon_load_template_part('footer.php'); 
