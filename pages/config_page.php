<?php
# Re-Theming Plugin - Configuration Page

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'config_title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

# Read the site-wide defaults explicitly (ALL_USERS), not the value
# resolved for the admin's own account - those are two different things
# and the current user may well have their own personal override set.
$t_current_theme = plugin_config_get( 'theme', 'default', false, ALL_USERS );
$t_current_modernize = plugin_config_get( 'modernize', OFF, false, ALL_USERS );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">
<form action="<?php echo plugin_page( 'config' ) ?>" method="post">
<fieldset>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<?php print_icon( 'fa-paint-brush', 'ace-icon' ); ?>
		<?php echo plugin_lang_get( 'config_title' ) ?>
	</h4>
</div>

<?php echo form_security_field( 'plugin_ReTheming_config' ) ?>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

	<tr>
		<td class="category"><?php echo plugin_lang_get( 'default_theme' ) ?></td>
		<td>
			<select id="theme" name="theme" class="input-sm">
				<?php foreach( ReThemingPlugin::get_themes() as $t_key => $t_definition ) { ?>
				<option value="<?php echo $t_key ?>" <?php echo $t_current_theme == $t_key ? 'selected' : '' ?>><?php echo plugin_lang_get( $t_definition['label'] ) ?></option>
				<?php } ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="category"><?php echo plugin_lang_get( 'default_modernize' ) ?></td>
		<td>
			<label class="inline">
				<input type="checkbox" class="ace" id="modernize" name="modernize" <?php check_checked( (int)$t_current_modernize, ON ); ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get( 'action_update' ) ?>" />
</div>
</div>
</div>
</fieldset>
</form>
</div>
</div>

<?php
layout_page_end();
