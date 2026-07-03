<?php
# Re-Theming Plugin - Configuration Save Handler

form_security_validate( 'plugin_ReTheming_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$t_theme = gpc_get_string( 'theme', 'default' );
if( !array_key_exists( $t_theme, ReThemingPlugin::get_themes() ) ) {
	$t_theme = 'default';
}

# Written with an explicit ALL_USERS scope: this is the site-wide default,
# not a personal override for whichever admin happens to submit this form.
if( $t_theme != plugin_config_get( 'theme', 'default', false, ALL_USERS ) ) {
	plugin_config_set( 'theme', $t_theme, ALL_USERS );
}

$t_modernize = gpc_get_bool( 'modernize' ) ? ON : OFF;
if( $t_modernize != plugin_config_get( 'modernize', OFF, false, ALL_USERS ) ) {
	plugin_config_set( 'modernize', $t_modernize, ALL_USERS );
}

form_security_purge( 'plugin_ReTheming_config' );

$t_redirect_url = plugin_page( 'config_page', true );
print_header_redirect( $t_redirect_url );
