# MantisBT Re-Theming Plugin

**Version 1.0.0**

Lets an administrator pick a site-wide default theme for MantisBT, and lets
each user override it with their own choice from the same curated list of
themes - without touching MantisBT's core files. A separate "Modernize"
checkbox layers a color-agnostic structural refresh (rounded corners,
softer shadows, more breathing room) on top of whichever theme is active.

Requirements
============
* MantisBT 2.20.0 or higher. **Developed and tested against MantisBT 2.28.4.**

Setup
=====
1. Clone/extract this repository into `mantis/plugins/ReTheming/` (the
   folder name **must** be `ReTheming` - it doubles as the plugin's
   basename).
2. In MantisBT, go to **Manage → Manage Plugins** and install/enable
   **Re-Theming**.
   * The "Manage Plugins" link only appears in the Manage menu for accounts
     at the Administrator access level (`manage_plugin_threshold`,
     default `ADMINISTRATOR`); if you don't see it, that's why.
3. Open the plugin's config page (linked from Manage Plugins, or directly at
   `plugin.php?page=ReTheming/config_page`) and pick the site-wide
   **Default Theme**, and optionally check **Modernize by Default**.
4. Any user can override either of those for themselves from **My Account
   → Preferences**, next to the existing timezone/language/font options.

How it works
============
Themes are plain CSS files under `files/themes/`, linked in via
`EVENT_LAYOUT_RESOURCES`, which fires right before `</head>` - after
MantisBT has already printed Bootstrap, `ace.css`, `ace-skins.css` and
`ace-mantis.css`. That ordering matters: several of those stock
stylesheets use bare `!important` rules (e.g. `table { background-color:
#fff !important }`), so a theme stylesheet has to load *after* them to
actually win the cascade. No core files are patched.

The site-wide default and each user's personal override are both stored
under a single plugin config option (`theme`), scoped globally or to a
specific user id respectively. MantisBT's own config resolution (user
value, falling back to the global one) does the rest - this is the exact
same pattern core uses for the `font_family` preference.

The per-user dropdown also offers **Inherit (site default)**, a sentinel
value that's only ever stored per-user (never as the site-wide default
itself). Unlike a user who's simply never touched the dropdown - who also
currently falls back to the site default, but only incidentally - a user
who explicitly picks Inherit keeps re-resolving to whatever the site
default *currently* is on every page load, including after an admin
changes it later.

Built-in themes
================
| Theme | Description |
| --- | --- |
| Default | MantisBT's stock look, unmodified. |
| Dark | In-house blue-gray dark palette. |
| Dracula | [draculatheme.com](https://draculatheme.com) palette. |
| Nord | [nordtheme.com](https://www.nordtheme.com) palette. |
| One Dark | Atom editor's default dark theme palette. |
| Gruvbox Dark | [morhetz/gruvbox](https://github.com/morhetz/gruvbox) palette. |
| Solarized Dark | Ethan Schoonover's Solarized palette. |

All dark-style themes share one structural stylesheet
(`files/themes/_base.css`), which is the only place that knows *which*
MantisBT selectors need overriding. Each theme is then just a small
`files/themes/palette-*.css` file that sets ~20 `--rt-*` custom properties
(background/surface/border/text/accent tones) - `_base.css` and the
palette file are both linked in together by `on_layout_resources()`.

Adding a new theme means: add a `palette-<key>.css` file (copy an existing
one and swap the hex values), a lang string for its label, and one entry
in the `$themes` registry in `ReTheming.php` - no changes to `_base.css`
or any other wiring needed.

Modernize
=========
`files/modernize.css` is a separate, color-agnostic stylesheet: corner
radius, box-shadow, padding, transitions - no `background-color`/`color`/
`border-color` rules at all. That's what lets it layer on top of Default
*or* any color theme without needing its own palette or any awareness of
which one (if any) is active. It's controlled by its own `modernize`
plugin config option, resolved and overridden exactly like `theme`, and
loaded after the theme's stylesheets (if any) by the same
`on_layout_resources()` hook.

Configuration reference
========================
| Option | Description |
| --- | --- |
| Default Theme | Site-wide theme applied to users who haven't picked one of their own. |
| Modernize by Default | Site-wide default for the Modernize checkbox. |
| Theme (My Account → Preferences) | Per-user override of the site-wide default theme. |
| Modernize (My Account → Preferences) | Per-user override of the site-wide Modernize default. |
