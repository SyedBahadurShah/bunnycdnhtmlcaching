<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.gulshankumar.net/about/
 * @since      1.0.0
 *
 * @package    Bunnycdnhtmlcaching
 * @subpackage Bunnycdnhtmlcaching/admin/partials
 */


/* This file should primarily consist of HTML with a little bit of PHP. */
?>

<div class="wrap">

	<h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

	<form method="post" action="options.php">

		<?php settings_fields( 'bunnycdnhtmlcaching_settings' ); ?>
		<?php do_settings_sections( 'bunnycdnhtmlcaching' ); ?>
		<?php submit_button("Setup Pullzone"); ?>
		<?php bunnycdnhtmlcaching_dns_ssl_html(); ?>

	</form>

</div>
