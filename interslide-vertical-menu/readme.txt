=== Interslide Vertical Menu ===
Contributors: interslide
Tags: menu, sidebar, navigation, accessibility
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Add a minimal, brut-style vertical menu inspired by brute.media. Includes global injection, shortcode, and block output.

== Installation ==
1. Upload the `interslide-vertical-menu` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to Settings → Interslide Vertical Menu and configure the menu.

== Usage ==
* Global mode: Enable the menu and select “Global (auto inject)”.
* Shortcode mode: Use `[interslide_vertical_menu mode="fixed"]` or `[interslide_vertical_menu mode="drawer" width="280" theme="light"]`.
* Block mode: Add the “Interslide Menu” block in the editor.

== FAQ ==
= Does the plugin include a search field? =
Yes, enable the inline search mode in the settings or use a search link.

== Changelog ==
= 1.0.0 =
* Initial release.

== Checklist ==
* Desktop: menu fixed, content shifted, icons visible.
* Mobile: drawer opens/closes with overlay and ESC.
* Focus: keyboard navigation and focus trap in drawer.
* Pages: homepage, posts, search page.
* Compatibility: classic and block themes.
