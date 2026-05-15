# Better Bookmarks

A WordPress block that fetches Open Graph metadata from a URL and renders a link preview card.

![Better Bookmarks block showing a link preview card](assets/screenshot-1.png)

## Requirements

- WordPress 6.5+
- PHP 7.2+
- Node.js 20+ (for building from source)

## Installation

Download the latest `better-bookmarks.zip` from the [Releases page](https://github.com/philhoyt/BetterBookmarks/releases), then install it from **Plugins > Add New Plugin > Upload Plugin** in your WordPress admin.

## Building from Source

```bash
npm install && npm run build
```

Activate the plugin from the WordPress admin. The `build/` directory must be present.

## Usage

Add the **Link Card** block from the block inserter. Paste a URL into the placeholder input and press Enter. The block fetches metadata via the REST API, stores it as block attributes, and renders server-side on the frontend.

Once a preview is loaded, the **Styles** tab in the block inspector gives you:

- Aspect ratio (pulls from your theme's registered ratios, same as the core Image block)
- Image fit (cover or contain)
- Typography, color, border, shadow, spacing

The editor preview is not clickable. The frontend card opens the URL in a new tab.

**Style variations:** Default, Compact (horizontal thumbnail), Compact Stacked (vertical with constrained width), Minimal (domain and title only).

## TMDb Integration

For IMDb URLs, the plugin can pull richer metadata (title, description, poster) from TMDb instead of scraping the IMDb page. Add your TMDb API key in **Settings > Better Bookmarks**, or define it in `wp-config.php`:

```php
define( 'BETTER_BOOKMARKS_TMDB_API_KEY', 'your-key-here' );
```

## REST API

`GET /wp-json/better-bookmarks/v1/preview?url={url}`

Requires `edit_posts` capability. The URL must be a valid public http/https address. Private and reserved IP ranges are blocked.

```json
{
  "url": "https://example.com",
  "title": "Page title",
  "description": "OG or meta description, truncated to 200 characters.",
  "image": "https://example.com/image.jpg",
  "domain": "example.com",
  "imageWidth": 1200,
  "imageHeight": 630
}
```

Image dimensions come from `og:image:width`/`og:image:height` tags when present. If absent, the endpoint fetches the first 32 KB of the image to read the file header (5-second timeout). If that fails, dimensions return as `0` and the block uses a 1.91:1 default aspect ratio.

## Limitations

- Metadata is fetched at edit time and stored in block attributes. The card does not update if the linked page changes.
- The REST endpoint fetches pages server-side, so it will not see content injected by JavaScript.
- Meta tag parsing uses regex, not a DOM parser. Malformed HTML may cause silent extraction failures.
- No caching. Each fetch is a fresh HTTP request.
- Description is hard-truncated at 200 characters server-side.

## Development

```bash
npm start              # Webpack watch mode
npm run build          # Production build
npm run lint           # ESLint + Stylelint
npm run test:js        # Jest unit tests
composer run lint      # PHPCS (WordPress Coding Standards)
composer run test      # PHPUnit
```

Source files are in `src/blocks/link-card/`. Built output goes to `build/`.

## Releases

Releases are published automatically when a version tag is pushed:

```bash
git tag v1.2.0 && git push origin v1.2.0
```

The CI workflow builds the assets, packages the plugin zip via `wp-scripts plugin-zip`, and attaches both the zip and `readme.txt` to the GitHub release.
