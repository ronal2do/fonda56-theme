<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <title><?php wp_title(''); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

  <!-- favicons -->
  <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/favicon.ico" />
  <link rel=”apple-touch-icon” sizes=”114×114″ href=”<?php echo get_template_directory_uri(); ?>/assets/img/favicon/touch-icon-114.png” />
  <link rel=”apple-touch-icon” sizes=”72×72″ href=”<?php echo get_template_directory_uri(); ?>/assets/img/favicon/touch-icon-72.png” />
  <link rel=”apple-touch-icon” href=”<?php echo get_template_directory_uri(); ?>/assets/img/favicon/touch-icon-57.png” />

  <?php wp_head(); ?>

  <!--[if lt IE 9]>
	<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7/html5shiv.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.3.0/respond.js"></script>
  	<link href="<?php echo get_template_directory_uri(); ?>/assets/css/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />
  <![endif]-->

  <link rel="alternate" type="application/rss+xml" title="<?php echo get_bloginfo('name'); ?> Feed" href="<?php echo home_url(); ?>/feed/">
</head>
