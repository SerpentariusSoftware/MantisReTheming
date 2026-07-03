# Changelog

## Unreleased

- Fixed three contrast/color bugs found on the Manage Plugins and My
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
- Added five more themes: Dracula, Nord, One Dark, Gruvbox Dark, and
  Solarized Dark, all using their respective projects' canonical color
  values.
- Refactored theme CSS into a shared `_base.css` (selector overrides,
  written against `--rt-*` custom properties) plus one small
  `palette-*.css` file per theme (just the property values) - adding a
  theme is now a ~20-line palette file instead of a ~300-line stylesheet.
- Fixed the theme stylesheet not actually applying: it was injected via
  `require_css()` on `EVENT_CORE_READY`, which MantisBT prints in
  `html_css()` *before* `layout_head_css()` loads Bootstrap/ace.css/
  ace-mantis.css - so every `!important` rule in this plugin's theme lost
  the cascade to MantisBT's own later, equally-`!important` rules (most
  visibly the global `table { background-color: #fff !important }`).
  Switched to `EVENT_LAYOUT_RESOURCES`, which fires right before
  `</head>`, after all of MantisBT's own stylesheets.
- Expanded Dark theme coverage: tables (MantisBT's `table { background:
  #fff !important }` was overriding the previous pass entirely), navbar,
  sidebar active/hover states, breadcrumb search box, badges, `.well`,
  pagination, ACE checkboxes/radios, disabled form controls, typeahead
  dropdowns, and the Bootstrap datetimepicker popup.
- Selector coverage cross-checked against the (unlicensed, so not copied
  from directly) `MantisBTDarkTheme` plugin as a "what needs recoloring"
  reference; colors/rules here are original.

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
