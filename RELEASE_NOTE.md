## Uguu 1.8.7

### Whats new

* Donation icons and copy icon have been replaced by SVG loaded in uguu.css to reduce amount of requests and data transfer needed when loading page.
* Preloading improvements for CSS and JS.
* "grillLoader" has been replaced with loading the grills via JS, which is controlled via the "GRILLS" array in `config.json` upon compilation.
* Upon compilation all assets will be pre-compressed using gzip so Nginx can serve them without having to compress them on runtime.
* Updated Nginx examples: faster TLS handshake, php extension re-write, allowing Nginx to serve pre-compressed assets.
* CSS fixes and improvements, better accessibility on mobile devices.
* The API no longer returns the original filename when uploading, since this can leak sensitive information.
* Assets such as the background image and the grills come pre-compressed in the AVIF format which further reduces load size, if a browser doesnt support AVIF it will fall back to PNG.
* Other minor things.

### Breaking changes

* `config.json` needs to be updated.
* Nginx config needs to be updated.

Recommended to do a clean install for this release, you may keep your existing database and all of your config.json settings.