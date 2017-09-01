<?php
/*
Plugin Name: Events Post Type
Plugin URI: https://github.com/afpdigital/events-post-type
Description:Add an events CPT to your WordPress site. Requires ACF.
Author: Matt Mirus
Author URI: https://github.com/afpdigital
Version: 0.1
GitHub Plugin URI: https://github.com/afpdigital/events-post-type
*/

namespace EPT;

register_activation_hook(__FILE__, __NAMESPACE__.'\\flush_rewrite_rules');

// actions
add_action('plugins_loaded', __NAMESPACE__.'\\acf_check');
add_action('acf/init', __NAMESPACE__.'\\acf_google_api');
add_action('init', __NAMESPACE__.'\\event_post_type', 0);
add_action('manage_posts_custom_column' , __NAMESPACE__.'\\custom_event_columns', 10, 2);
add_action('pre_get_posts', __NAMESPACE__.'\\sort_event_archives');
add_action('pre_get_posts', __NAMESPACE__.'\\filter_event_archive');
add_action('base_before_template', __NAMESPACE__.'\\add_events_menu');

// filters
add_filter('acf/settings/load_json', __NAMESPACE__.'\\acf_add_json_load_point');
add_filter('manage_event_posts_columns' , __NAMESPACE__.'\\add_event_columns');
add_filter('manage_edit-event_sortable_columns', __NAMESPACE__.'\\event_sortable_columns');
add_filter('request', __NAMESPACE__.'\\event_sort_by');
add_filter('the_content', __NAMESPACE__.'\\eventbrite_iframe');
add_filter('get_the_archive_title', __NAMESPACE__.'\\filter_event_archive_title');

// Check if Advanced Custom Fields is loaded and deactivate w/ a message if not
function acf_check() {
	if(! class_exists('acf')) {
		add_action('admin_init', __NAMESPACE__.'\\deactivate');
		add_action('admin_notices', __NAMESPACE__.'\\admin_notice_no_acf');
	}
}

// deactivate this plugin
function deactivate() {
	deactivate_plugins(plugin_basename(__FILE__));
}

// notify that ACF is required and plugin has been deactivated
function admin_notice_no_acf() {
	printf('<div class="error notice is-dismissible"><p class="extension-message"><strong>Advanced Custom Fields Pro</strong> is required for the Event Post Type plugin. Deactivating the <strong>Event Post Type</strong> plugin.</p></div>');
}

// Append this plugin's ACF JSON field definitions load point to list of load points
function acf_add_json_load_point($paths) {
	$paths[] = plugin_dir_path(__FILE__) . '/acf-json';

	return $paths;
}

// add options page
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Event Post Type',
		'menu_title'	=> 'Event Post Type',
		'parent_slug'	=> 'options-general.php',
	));
}

// Google API key for Google Maps
function acf_google_api() {
	$maps_key = get_field('ept_maps_key', 'option');
	
	if (!empty($maps_key)) {
		acf_update_setting('google_api_key', $maps_key);
	}
}

// flush rewrite rules after adding post type for first time
function flush_rewrite_rules() {
  event_post_type();

  \flush_rewrite_rules();
}

// Register custom post type for events
function event_post_type() {
	$labels = array(
		'name'                  => 'Events',
		'singular_name'         => 'Event',
		'menu_name'             => 'Events',
		'name_admin_bar'        => 'Event',
		'archives'              => 'Event Archives',
		'parent_item_colon'     => '',
		'all_items'             => 'Events',
		'add_new_item'          => 'Add New Event',
		'add_new'               => 'Add New',
		'new_item'              => 'New Event',
		'edit_item'             => 'Edit Event',
		'update_item'           => 'Update Event',
		'view_item'             => 'View Event',
		'search_items'          => 'Search Event',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into event',
		'uploaded_to_this_item' => 'Uploaded to this event',
		'items_list'            => 'Events list',
		'items_list_navigation' => 'Events list navigation',
		'filter_items_list'     => 'Filter events list',
	);
  $rewrite = array(
		'slug'                  => 'events',
		'with_front'            => true,
		'pages'                 => true,
		'feeds'                 => true,
	);
	$args = array(
		'label'                 => 'Event',
		'description'           => 'Events',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', ),
		'taxonomies'            => array( 'category', ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-calendar',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
    'rewrite'               => $rewrite,
		'capability_type'       => 'page',
	);
	register_post_type( 'event', $args );
}

// Add custom admin columns
function add_event_columns($columns) {
    // remove unwanted columns
    unset($columns['date']);

    // add new columns
  $columns = array_merge($columns, [
      'date_start' => 'Start Date',
    ]);

    // move some columns to end
    $end_cols = [ 'categories', ];
    foreach ($end_cols as $col) {
      $v = $columns[$col];
      unset($columns[$col]);
      $columns[$col] = $v;
    }

    return $columns;
}

// Add data to custom admin columns
function custom_event_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'date_start':
      $dt = new \DateTime(get_field('date_start', $post_id));
			echo $dt->format('F j, Y');
			break;
	}
}

// Make custom admin columns sortable
function event_sortable_columns() {
  return array(
    'date_start'  => 'date_start',
  );
}

// Sort by...
function event_sort_by( $vars ) {
  global $pagenow;

  if ( is_admin() && 'edit.php' == $pagenow && isset($_GET['post_type']) && $_GET['post_type'] == 'event') {
    $date_sort = [
      'meta_key' => 'date_start',
      'orderby' => 'meta_value',
      'meta_type' => 'DATE',
    ];

    if ( isset( $vars['orderby'] ) ) {
      if ($vars['orderby'] == 'date_start') {
        $vars = array_merge( $vars, $date_sort);
      }
    }
    else {
      $date_sort['order'] = 'DESC';
      $vars = array_merge( $vars, $date_sort);
    }
  }

  return $vars;
}

/**
 * Get instance of EST DateTimeZone object
 */
function est_time_zone() {
  return new \DateTimeZone('America/New_York');
}

/**
 * Get current date and time in EST
 *
 * @return    DateTime instance
 */
function now_est() {
  $dtz = est_time_zone();
  return new \DateTime('now', $dtz);
}

/**
 * Get absolute value of date offset between GMT and EST in seconds
 */
function gmt_offset_seconds() {
  return abs(est_time_zone()->getOffset( now_est() ));
}

/**
 * Get current date in format used by the event date fields (YYYYMMDD)
 * @return    string YYYYMMDD
 */
function today_event_format() {
  return now_est()->format('Ymd');
}

// sort events archive by start date, and show only upcoming events
function sort_event_archives($query) {
  if (is_post_type_archive('event') && !is_admin() && $query->is_main_query()) {
    $query->set('meta_key', 'date_start');
	  $query->set('order', 'ASC');
    $query->set('orderby', 'meta_value');
    $query->set('meta_type', 'DATE');
  }
}



// filter events archive to show only upcoming events
function filter_event_archive($query) {
  if (is_post_type_archive('event') && !is_admin() && $query->is_main_query()) {
    $meta_query = array();

    $today_event_format = today_event_format();

    $meta_query[] = [
      'relation' => 'OR',
      [
        'key' => 'date_start',
        'value' => $today_event_format,
        'type' => 'NUMERIC',
        'compare' => '>=',
      ],
      [
        'key' => 'date_end',
        'value' => $today_event_format,
        'type' => 'NUMERIC',
        'compare' => '>=',
      ],
    ];

    if( count( $meta_query ) > 0 ){
      $query->set( 'meta_query', $meta_query );
    }
  }
}

// add events menu to events archive
function add_events_menu() {
  if (is_post_type_archive('event')) {
    get_template_part('templates/events-menu');
  }
}

// add Eventbrite registration iframe to single event posts
function eventbrite_iframe($content) {
  if (!is_singular('event')) return $content;

  $eventbrite_id = get_field('eventbrite_id');

  if (!$eventbrite_id) return $content;

  ob_start();
  ?>
  <?php
	$eid = intval($eventbrite_id);
	if ( is_int($eid) && $eid != 0 ) :
    ?>
		<div style="width:100%; text-align:left;" >
			<iframe src="//eventbrite.com/tickets-external?eid=<?= $eid ?>&ref=etckt" frameborder="0" height="250" width="100%" vspace="0" hspace="0" marginheight="5" marginwidth="5" scrolling="auto" allowtransparency="true" class="eventbrite-embed"></iframe>
		</div>
    <?php
	endif;

  $content .= ob_get_clean();

  return $content;
}

/**
* Remove 'Archives: ' from the event archives
*/
function filter_event_archive_title( $title ) {
  if (is_post_type_archive('event')) {
    $title = post_type_archive_title('', false);
  }
  return $title;
}
