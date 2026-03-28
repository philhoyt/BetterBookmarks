=== Better Bookmarks ===
Contributors: philhoyt
Tags: block, bookmark, link-card, open-graph, gutenberg
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress block that fetches Open Graph metadata from a URL and renders a link preview card.

== Description ==

Better Bookmarks adds a single block — Link Card — that fetches Open Graph metadata from any URL and renders a styled preview card showing the page image, title, description, and domain.

Metadata is fetched server-side via a REST endpoint at edit time and stored as block attributes. The card renders server-side on the frontend.

**Features**

* Fetches og:title, og:description, og:image, og:image:width, og:image:height
* Falls back to <title> and <meta name="description"> when OG tags are absent
* Four style variations: Default, Compact, Compact Stacked, and Minimal
* Image aspect ratio control using the same presets as the core Image block
* Defaults to 1.91:1 (the OG image spec recommendation)
* Block alignment controls (left, center, right, wide, full)
* Full block supports: color, border, shadow, padding, anchor
* Transform from core Embed block — URL carries over automatically

**Limitations**

* Metadata is stored at edit time. The card does not update automatically if the linked page changes.
* The REST endpoint fetches pages server-side and will not see JavaScript-rendered content.
* No caching. Each preview fetch is a live HTTP request.
* Requires the Block Editor. Not compatible with the Classic Editor.

== Installation ==

1. Upload the `better-bookmarks` folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins > Installed Plugins**.

The plugin requires pre-built assets in the `build/` directory. If you are installing from source, run `npm install && npm run build` before activating.

== Frequently Asked Questions ==

= The preview didn't load. What happened? =

The REST endpoint (`/wp-json/better-bookmarks/v1/preview`) requires the `edit_posts` capability. It also makes an outbound HTTP request to the target URL with a 10-second timeout. If the target site blocks server-side requests or responds slowly, the fetch will fail.

= The image dimensions are wrong or missing. =

Dimensions come from `og:image:width` and `og:image:height` meta tags. If the target page doesn't include them, the endpoint falls back to `getimagesize()`, which requires `allow_url_fopen` to be enabled in PHP. If that's disabled on your server, dimensions fall back to 0 and the default 1.91:1 aspect ratio is used.

= Can I change the aspect ratio? =

Yes. Once a preview is loaded, open the block inspector and switch to the **Styles** tab. An aspect ratio selector appears in the Image panel. Options are pulled from your theme's registered aspect ratios (the same list used by the core Image block).

= Does this work with the Classic Editor? =

No.

== Changelog ==

= 1.0.4 =
* Limit link card title to two lines with ellipsis truncation.

= 1.0.3 =
* Changed image wrap background color to white.

= 1.0.2 =
* Removed deprecated `load_plugin_textdomain()` call; WordPress handles translation loading automatically since 4.6.
* Renamed render.php variables to use the full `better_bookmarks_` prefix to satisfy Plugin Check static analysis.

= 1.0.1 =
* Added four style variations: Default, Compact, Compact Stacked, and Minimal.
* Added block supports: color (background and text), border, shadow, padding, anchor, and renaming.
* Added alignment controls: left, center, right, wide, and full.
* Added block transform from core Embed block with automatic metadata fetch.
* Moved image aspect ratio control to the Styles tab in the block inspector.
* Hardened REST endpoint against SSRF: blocks non-http/s schemes, private and reserved IP ranges, and limits response size to 2 MB.
* Added automatic update checks via GitHub releases.
* Bumped minimum WordPress version to 6.5.

= 1.0.0 =
* Initial release.
