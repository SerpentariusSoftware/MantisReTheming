<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Re-Theming Plugin
 *
 * Lets an administrator pick a site-wide default theme, and lets each user
 * override it with their own choice from the same curated list. Themes are
 * plain CSS files injected on top of MantisBT's stock stylesheets via the
 * core require_css() mechanism - no core files are modified.
 *
 * Resolution of "which theme applies" is delegated entirely to MantisBT's
 * own config layering (user override falling back to the site-wide
 * default): both are stored under the same 'theme' plugin config option,
 * scoped to a user id or left global, exactly like core prefs such as
 * font_family.
 */
class ReThemingPlugin extends MantisPlugin {
	/**
	 * Sentinel 'theme' value: only ever stored against a specific user id
	 * (My Account > Preferences), never as the site-wide default (it's
	 * not offered on the admin config page). Means "no personal
	 * preference - always resolve to whatever the site-wide default is
	 * *at page-render time*". A user with no row at all already falls
	 * back to the site-wide default too, but only as read; explicitly
	 * storing this instead means a user who *had* set a concrete theme
	 * before can deliberately revert to following the site default again,
	 * and it stays that way rather than being a one-time snapshot.
	 */
	const THEME_INHERIT = 'inherit';

	/**
	 * Curated theme registry: key => [ lang string key for its label,
	 * palette stylesheet path relative to files/themes/ (or null for
	 * MantisBT's unmodified default look, which needs no extra
	 * stylesheet) ]. Every non-null entry is paired with files/themes/
	 * _base.css, which holds the actual selector overrides - palette
	 * files only set the --rt-* custom properties _base.css reads.
	 */
	private static $themes = array(
		'default' => array(
			'label' => 'theme_default',
			'css' => null,
		),
		'dark' => array(
			'label' => 'theme_dark',
			'css' => 'palette-dark.css',
		),
		'dracula' => array(
			'label' => 'theme_dracula',
			'css' => 'palette-dracula.css',
		),
		'nord' => array(
			'label' => 'theme_nord',
			'css' => 'palette-nord.css',
		),
		'one_dark' => array(
			'label' => 'theme_one_dark',
			'css' => 'palette-one-dark.css',
		),
		'gruvbox_dark' => array(
			'label' => 'theme_gruvbox_dark',
			'css' => 'palette-gruvbox-dark.css',
		),
		'solarized_dark' => array(
			'label' => 'theme_solarized_dark',
			'css' => 'palette-solarized-dark.css',
		),
	);

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = 'config_page';

		$this->version = '1.0.0';
		$this->requires = array(
			'MantisCore' => '2.20.0',
		);

		$this->author = 'MantisBT Local';
		$this->contact = 'admin@mantis.local';
		$this->url = 'https://mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array(
			# Theme key, see self::$themes. Stored globally (site-wide
			# default, set from the admin config page) or per-user (set
			# from My Account > Preferences) - both live under this same
			# option name, MantisBT's config resolution picks the user's
			# row over the global one automatically.
			'theme' => 'default',

			# Whether the "Modernize" structural refresh (spacing, corner
			# radius, shadows, no other color-scoped choices) is layered on
			# top of the color theme above. Same global-default/per-user-
			# override pattern as 'theme'.
			'modernize' => OFF,
		);
	}

	/**
	 * Register event hooks for plugin.
	 * @return array
	 */
	function hooks() {
		return array(
			'EVENT_LAYOUT_RESOURCES' => 'on_layout_resources',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM' => 'on_account_pref_update_form',
			'EVENT_ACCOUNT_PREF_UPDATE' => 'on_account_pref_update',
		);
	}

	/**
	 * Print the effective theme's stylesheet link (current user's
	 * override, or the site-wide default if they haven't set one).
	 *
	 * This has to be EVENT_LAYOUT_RESOURCES rather than require_css() on
	 * EVENT_CORE_READY: layout_page_header_begin() calls html_css() (which
	 * is what prints require_css()'d stylesheets) *before*
	 * layout_head_css() (which loads ace.css/ace-skins.css/ace-mantis.css).
	 * Since several of those stock stylesheets use bare !important rules
	 * (e.g. "table { background-color: #fff !important }"), a stylesheet
	 * loaded earlier in the cascade loses every one of those ties.
	 * EVENT_LAYOUT_RESOURCES fires right before </head>, after all of
	 * that, so our overrides load last and actually win.
	 * @return string
	 */
	function on_layout_resources() {
		$t_html = '';

		$t_theme_key = plugin_config_get( 'theme' );
		if( self::THEME_INHERIT === $t_theme_key ) {
			# A user explicitly chose "Inherit"; re-resolve against the
			# site-wide default only, so they keep following it even as an
			# admin changes it later, rather than freezing on whatever it
			# happened to be when they picked Inherit.
			$t_theme_key = plugin_config_get( 'theme', 'default', false, ALL_USERS );
		}

		$t_theme = $this->theme_definition( $t_theme_key );
		if( $t_theme['css'] !== null ) {
			$t_html .= $this->stylesheet_link( 'themes/_base.css' );
			$t_html .= $this->stylesheet_link( 'themes/' . $t_theme['css'] );
		}

		# Modernize is a structural refresh (spacing/radius/shadows), not a
		# color choice, so it's independent of - and loads after, in case
		# it needs to soften something a theme set - whichever theme (or
		# none) is active above.
		if( ON == plugin_config_get( 'modernize' ) ) {
			$t_html .= $this->stylesheet_link( 'modernize.css' );
		}

		return $t_html;
	}

	/**
	 * Build a <link> tag for a plugin-relative stylesheet path.
	 *
	 * plugin_file.php serves these with "Cache-Control: private, max-age=
	 * 10800" (3 hours) and a URL that never otherwise changes, so without
	 * a cache-busting query string, browsers keep serving an edited
	 * theme's *previous* content for up to 3 hours after the file on disk
	 * changes. Tagging the URL with the plugin version forces a fresh
	 * fetch whenever that version is bumped.
	 * @param string $p_path Path relative to files/, e.g. 'themes/_base.css'.
	 * @return string
	 */
	private function stylesheet_link( $p_path ) {
		$t_url = helper_url_combine( plugin_file( $p_path ), array( 'v' => $this->version ) );
		return '<link rel="stylesheet" type="text/css" href="' . string_attribute( $t_url ) . '" />' . "\n";
	}

	/**
	 * EVENT_ACCOUNT_PREF_UPDATE_FORM hook: add a theme selector row to the
	 * My Account > Preferences form, alongside timezone/language/font.
	 * @param string  $p_event   Event name.
	 * @param integer $p_user_id User id whose preferences are being edited.
	 * @return void
	 */
	function on_account_pref_update_form( $p_event, $p_user_id ) {
		$t_current_theme = plugin_config_get( 'theme', 'default', false, $p_user_id );
		$t_current_modernize = plugin_config_get( 'modernize', OFF, false, $p_user_id );
		?>
		<tr>
			<td class="category">
				<label for="theme"><?php echo plugin_lang_get( 'theme' ) ?></label>
			</td>
			<td>
				<select id="theme" name="theme" class="input-sm">
					<option value="<?php echo self::THEME_INHERIT ?>" <?php echo $t_current_theme == self::THEME_INHERIT ? 'selected' : '' ?>><?php echo plugin_lang_get( 'theme_inherit' ) ?></option>
					<?php foreach( self::$themes as $t_key => $t_definition ) { ?>
					<option value="<?php echo $t_key ?>" <?php echo $t_current_theme == $t_key ? 'selected' : '' ?>><?php echo plugin_lang_get( $t_definition['label'] ) ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo plugin_lang_get( 'modernize' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace" id="modernize" name="modernize" <?php check_checked( (int)$t_current_modernize, ON ); ?> />
					<span class="lbl"></span>
				</label>
			</td>
		</tr>
		<?php
	}

	/**
	 * EVENT_ACCOUNT_PREF_UPDATE hook: persist the user's chosen theme.
	 * @param string  $p_event   Event name.
	 * @param integer $p_user_id User id whose preferences were submitted.
	 * @return void
	 */
	function on_account_pref_update( $p_event, $p_user_id ) {
		$t_theme = gpc_get_string( 'theme', 'default' );
		if( self::THEME_INHERIT !== $t_theme && !isset( self::$themes[$t_theme] ) ) {
			$t_theme = 'default';
		}

		if( $t_theme != plugin_config_get( 'theme', 'default', false, $p_user_id ) ) {
			plugin_config_set( 'theme', $t_theme, $p_user_id );
		}

		$t_modernize = gpc_get_bool( 'modernize' ) ? ON : OFF;
		if( $t_modernize != plugin_config_get( 'modernize', OFF, false, $p_user_id ) ) {
			plugin_config_set( 'modernize', $t_modernize, $p_user_id );
		}
	}

	/**
	 * Look up a theme's definition, falling back to 'default' for an
	 * unrecognized/unset value.
	 * @param string $p_key Theme key.
	 * @return array
	 */
	private function theme_definition( $p_key ) {
		return self::$themes[$p_key] ?? self::$themes['default'];
	}

	/**
	 * The curated theme registry, for pages (e.g. the admin config page)
	 * that need to list the available choices.
	 * @return array
	 */
	public static function get_themes() {
		return self::$themes;
	}
}
