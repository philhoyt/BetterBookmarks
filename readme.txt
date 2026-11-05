=== Better Bookmarks ===
Contributors: philhoyt
Tags: block, bookmark, link-card, open-graph, gutenberg
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.2
Stable tag: 1.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Link Card block that fetches Open Graph metadata from a URL and renders a preview card

== Description ==

Better Bookmarks adds one block, Link Card. Paste a URL and the block fetches the page's Open Graph metadata and renders a card with the image, title, description, and domain. On the front end the card links to the URL in a new tab.

Metadata is fetched once, at edit time, through a REST endpoint on your own site, and stored in the block's attributes. The card is rendered server-side.

What the block reads from the page:

* og:title, og:description, og:image, og:image:width, and og:image:height.
* The `<title>` tag and `<meta name="description">` when the Open Graph tags are missing.

Block options:

* Four styles: Default, Compact, Compact Stacked, and Minimal.
* Image aspect ratio, using the same presets as the core Image block, with 1.91:1 as the default.
* Image fit (cover or contain) and a max width for the Compact Stacked style.
* Alignment: left, center, right, wide, and full.
* Block supports for color, border, shadow, padding, margin, typography, and anchor.
* A transform from the core Embed block; the URL carries over and the preview is fetched.

For IMDb URLs the block can read title, description, and poster from TMDb instead of the IMDb page, which blocks server-side requests. Enter a TMDb API key under Settings > Better Bookmarks, or define BETTER_BOOKMARKS_TMDB_API_KEY in wp-config.php.

Limitations:

* Metadata is stored at edit time. The card does not update when the linked page changes.
* The page is fetched server-side, so content added by JavaScript is not seen.
* No caching. Each preview fetch is a live HTTP request.
* Needs the block editor. The Classic Editor is not supported.

== Installation ==

1. Upload the `better-bookmarks` folder to `/wp-content/plugins/`, or install the release zip from the Plugins screen.
2. Activate the plugin from Plugins > Installed Plugins.

The plugin needs the built assets in `build/`. When installing from source, run `npm install && npm run build` first. Updates are delivered from GitHub releases through Plugin Update Checker.

== Frequently Asked Questions ==

= The preview did not load. What happened? =

The REST endpoint (`/wp-json/better-bookmarks/v1/preview`) requires the `edit_posts` capability. It requests the target URL from your server with a 10-second timeout and reads at most 2 MB. If the target site blocks server-side requests or responds slowly, the fetch fails.

= The image dimensions are wrong or missing. =

Dimensions come from the `og:image:width` and `og:image:height` tags. When those are missing, the endpoint fetches the first 32 KB of the image and reads its header, with a 5-second timeout. If that also fails, the dimensions are stored as 0 and the card uses the default 1.91:1 aspect ratio.

= Can I change the aspect ratio? =

Yes. After a preview has loaded, open the Styles tab in the block inspector. The Image panel lists the aspect ratios registered by your theme, the same list the core Image block uses.

= Does this work with the Classic Editor? =

No.

== Changelog ==

= 1.2.1 =
* Fixed update checks failing with a missing class: the update checker's Markdown and readme parsers now ship with the plugin.

= 1.2.0 =
* Replaced getimagesize() with a capped wp_remote_get() call for the image dimension probe: 5-second timeout, 32 KB body limit, Range header support.
* Added typography block support (font size, line height, font family, font weight).
* Replaced hardcoded colors in the stylesheet with CSS custom properties that fall back to --wp--preset--color--base and --wp--preset--color--contrast.
* Added an aria-label to the URL input in the block placeholder.
* Fixed get_imdb_id() to require a full domain match, so domains that only contain "imdb.com" as a substring no longer match.
* Switched the block.json $schema from trunk to stable.
* Changed update delivery to GitHub releases through Plugin Update Checker.
* Tested up to WordPress 7.1.

= 1.1.0 =
* Added a settings page under Settings > Better Bookmarks.
* Added a TMDb API key setting, set on the settings page or by defining BETTER_BOOKMARKS_TMDB_API_KEY in wp-config.php.
* Added TMDb lookups for IMDb links (title, description, poster) when an API key is set, since IMDb blocks server-side requests.
* Improved the error message when a remote server returns a non-200 status code.

= 1.0.5 =
* Added an image fit control (contain or cover) to the image settings panel, defaulting to cover.
* Added a max width control for the Compact Stacked style.
* Hid the aspect ratio control when the active style has a fixed aspect ratio.

= 1.0.4 =
* Limited the card title to two lines with ellipsis truncation.

= 1.0.3 =
* Changed the image wrap background color to white.

= 1.0.2 =
* Removed the deprecated load_plugin_textdomain() call; WordPress has loaded translations itself since 4.6.
* Renamed render.php variables to the full better_bookmarks_ prefix to satisfy Plugin Check.

= 1.0.1 =
* Added four styles: Default, Compact, Compact Stacked, and Minimal.
* Added block supports: color (background and text), border, shadow, padding, anchor, and renaming.
* Added alignment controls: left, center, right, wide, and full.
* Added a transform from the core Embed block that fetches metadata for the embedded URL.
* Moved the image aspect ratio control to the Styles tab in the block inspector.
* Hardened the REST endpoint against SSRF: blocked non-http/s schemes and private and reserved IP ranges, and capped the response size at 2 MB.
* Added update checks against GitHub releases.
* Raised the minimum WordPress version to 6.5.

= 1.0.0 =
* Initial release.
