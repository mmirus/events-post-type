# Events Post Type
Add an events CPT to your WordPress site. Requires ACF.

If needed, add support for additional taxonomies to the post type using the ```register_taxonomy_for_object_type``` function. See: https://codex.wordpress.org/Function_Reference/register_taxonomy_for_object_type.

If an ACF Google Maps API key is not already specified on your site, you can enter an API key authorized for use with the Google Maps JavaScript API and Google Geocoding API under Settings > Events Post Type.

Basic event archives and single event templates are provided and will be used by default. You will need to provide any styles you want applied.

If desired, you can override the included templates using the normal WordPress template hierarchy: add ```archive-event.php``` and ```single-event.php``` to your theme. You can copy the files from the plugin as starters.
