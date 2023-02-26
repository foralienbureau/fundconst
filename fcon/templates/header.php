<?php
/**
 * Global header
 */

$logo = get_option('options_logo_in_header');
$logo_link = home_url('/');

if( fcon_is_thankyou_mode() ) {
    $logo_link = get_permalink( get_queried_object() );
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="HandheldFriendly" content="True">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <?php wp_head(); ?>
</head>

<body
    x-data="{locked:false}"
    @body-lock.window="locked=true"
    @body-unlock.window="locked=false"
    :class="{'is-locked': locked}"
    id="top" <?php body_class(); ?>>

<div class="the-site">

<header class="fcon-header fcon-container">
    <div class="fcon-header__branding">
        <a href="<?php echo esc_url($logo_link); ?>" class="fcon-logo-link">
            <?php echo wp_get_attachment_image((int)$logo, 'full'); ?>
        </a>
    </div>
</header>

<div class="fcon-content fcon-container">
