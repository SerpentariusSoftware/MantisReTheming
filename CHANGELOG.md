# Changelog

## 0.4.0

- Added an "Inherit (site default)" option to the per-user Theme dropdown
  (My Account -> Preferences only, not the admin config page). Stores a
  sentinel value that always re-resolves to the site-wide default at
  render time, so a user who previously set a concrete theme can opt back
  into following the admin's choice going forward, instead of having to
  pick whatever that value currently happens to be.
- Fixed the login/signup/lost-password box (`#login-box`) blending into
  the page background on themes where `--rt-bg` and `--rt-surface` are
  the same color (Dracula, One Dark, Gruvbox Dark all intentionally reuse
  their source project's single canonical "background" for both): that
  box carries MantisBT's own `no-border` class, which zeroes
  border-*width* (not just color), so no border-color override could
  ever have shown through it regardless of theme.

## 0.3.0

- Added "Modernize": a structural refresh (rounded corners, softer
  shadows instead of flat borders, more breathing room, smoother
  transitions) that's independent of color choice - it's a checkbox, not
  a theme, and layers on top of Default or any color theme. Same
  global-default/per-user-override pattern as the theme picker (admin
  config page + My Account -> Preferences). Deliberately has no
  background-color/color/border-color rules of its own so it doesn't need
  a palette or interact with the theme system at all.
- Fixed the breadcrumb bar (and its "Recently Visited: <id>" text)
  staying light blue: `ace-skins.css` sets `.skin-3 .breadcrumbs {
  background-color: #E7F2F8 }`, which outranks a plain `.breadcrumbs`
  rule on specificity alone regardless of load order. Matched that
  specificity instead of reaching for `!important`.

## 0.2.0

- Fixed the theme stylesheet not actually applying: it was injected via
  `require_css()` on `EVENT_CORE_READY`, which MantisBT prints in
  `html_css()` *before* `layout_head_css()` loads Bootstrap/ace.css/
  ace-mantis.css - so every `!important` rule in this plugin's theme lost
  the cascade to MantisBT's own later, equally-`!important` rules (most
  visibly the global `table { background-color: #fff !important }`).
  Switched to `EVENT_LAYOUT_RESOURCES`, which fires right before
  `</head>`, after all of MantisBT's own stylesheets.
- Expanded Dark theme coverage: tables, navbar, sidebar active/hover
  states, breadcrumb search box, badges, `.well`, pagination, ACE
  checkboxes/radios, disabled form controls, typeahead dropdowns, and the
  Bootstrap datetimepicker popup. Selector coverage cross-checked against
  the (unlicensed, so not copied from directly) `MantisBTDarkTheme` plugin
  as a "what needs recoloring" reference; colors/rules here are original.
- Added five more themes: Dracula, Nord, One Dark, Gruvbox Dark, and
  Solarized Dark, all using their respective projects' canonical color
  values.
- Refactored theme CSS into a shared `_base.css` (selector overrides,
  written against `--rt-*` custom properties) plus one small
  `palette-*.css` file per theme (just the property values) - adding a
  theme is now a ~20-line palette file instead of a ~300-line stylesheet.
- Fixed three more contrast/color bugs found on the Manage Plugins and My
  Account pages:
  - `a:visited` was unstyled, so previously-visited links fell back to
    the browser's default purple instead of the theme's accent color.
  - The navbar's project switcher uses ACE's `.dropdown-yellow` utility
    class, which hardcodes its own hover/active background (`#FEE188`)
    independently of the generic `.dropdown-menu` rules - the "current
    project" entry was rendering with that stock yellow highlight.
    Its `<li>` items are also nested inside an extra `<ul class="list">`,
    which a `.dropdown-menu > li > a` direct-child selector never reached
    at all.
  - Read-only `.form-control` fields (e.g. Manage Columns' "All Available
    Columns" list) get a light gray background from Bootstrap with
    higher specificity than our plain `.form-control` rule, pairing our
    light theme text with a near-white background until focused.
- Theme stylesheet URLs now carry a `?v=<plugin version>` cache-busting
  parameter. `plugin_file.php` serves them with a 3-hour `Cache-Control:
  private, max-age=10800` and an otherwise-unchanging URL, so without
  this, an edited theme could keep rendering its previous content in an
  already-loaded browser for up to 3 hours.
- Styled Bootstrap `.alert` boxes (session-expired warnings, install
  warnings, form errors) - previously unstyled on the login/signup/
  lost-password pages.
- Hardened `.widget-box`/`.widget-header`/`.widget-body`/`.widget-main`/
  `.widget-toolbox` with `!important`, and themed the login box's bottom
  toolbar (`.signup-box .toolbar`, hardcoded to `#393939` in
  ace-mantis.css).

## 0.1.0

Initial structure.

- Site-wide default theme, selectable by an administrator from the plugin's
  config page under Manage Plugins.
- Per-user theme override, selectable from My Account > Preferences,
  falling back to the site-wide default when unset.
- Two built-in themes: Default (MantisBT's stock look, untouched) and Dark
  (first-pass full dark palette).
- Themes are plain CSS files injected via MantisBT's core `require_css()`
  mechanism - no core files are modified.
- Translations: English, Spanish, German, Hungarian.
