FacetWP - Caching support
=======================

Make [FacetWP](https://facetwp.com/) pages load quicker with caching

## Installation
* Click the "Download ZIP" button on this page.
* Unzip the folder and rename it to "facetwp-cache"
* Upload the folder into the /wp-content/plugins/ directory
* Activate the plugin (FacetWP must also be active)
* Does `/wp-content/db.php` exist?
  * **No:** copy `facetwp-cache/db.php` into `/wp-content/db.php`
  * **Yes:** copy the code from `facetwp-cache/db.php` to the top of `/wp-content/db.php`
