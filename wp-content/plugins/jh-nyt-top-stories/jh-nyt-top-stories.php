<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.janushenderson.com/
 * @since             1.0.0
 * @package           Jh_Nyt_Top_Stories
 *
 * @wordpress-plugin
 * Plugin Name:       Janus Henderson NYT Top Stories
 * Plugin URI:        https://github.com/JanusHenderson/wp-skills-assessment
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Janus Henderson
 * Author URI:        https://www.janushenderson.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jh-nyt-top-stories
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'JH_NYT_TOP_STORIES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jh-nyt-top-stories-activator.php
 */
function activate_jh_nyt_top_stories() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jh-nyt-top-stories-activator.php';
	Jh_Nyt_Top_Stories_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jh-nyt-top-stories-deactivator.php
 */
function deactivate_jh_nyt_top_stories() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jh-nyt-top-stories-deactivator.php';
	Jh_Nyt_Top_Stories_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_jh_nyt_top_stories' );
register_deactivation_hook( __FILE__, 'deactivate_jh_nyt_top_stories' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-jh-nyt-top-stories.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_jh_nyt_top_stories() {

	$plugin = new Jh_Nyt_Top_Stories();
	$plugin->run();

}
run_jh_nyt_top_stories();


class NytTopStories {
	public function __construct()
	{
		add_action ('init', array($this, 'register_stories_cpt'));
		add_action('admin_menu', array($this, 'stories_add_menu_page'));
		
	}

	public function register_stories_cpt() {

		$args = array(
			'public' => true,
			'capability_type' => 'post',
			'exclude_from_search' => true,
			// 'publicly_queryable' => false,
			'labels' => array(
				'name' => 'Top Stories',
				'singular_name' => 'Top Story'
			),
			'taxonomies' => array('post_tag', 'category'),
			'menu_icon' => 'dashicons-media-text',
		);

		register_post_type('story', $args);
	}

	public function stories_add_menu_page() {
		add_menu_page(
			'NY Top Stories',
			'NY Top Stories',
			'manage_options',
			'jh-nyt-top-stories',
			// 'get_stories',
			'ping_api',
			'dashicons-book',
			16,
		);

		// function get_stories() {
		// 	echo "hello";
		// }

		function ping_api() {
			if(false === get_option('jh_nyt_top_stories')) {

				$top_stories = get_top_stories_from_api();

				add_option( 'jh_nyt_top_stories', $top_stories);

				// echo '<pre>';
				// var_dump( $results );
				// echo '</pre>';

				return;
			}

			// print_r('the option is missing');

			if ( get_option ('jh_nyt_table_version') ) {
				create_database_table();
			}
			
			//get info stored in the database
			save_database_table_info();
			
			// print_r('the option is saved');
		}

		function get_top_stories_from_api() {

			// $results = wp_remote_get('https://api.nytimes.com/svc/topstories/v2/home.json?api-key=yt0gtxAuAfZDItcull3vLJG7qjGPyjnN');
			$app_id = '95a07ae2-7467-4bf5-89fe-58553a10cbfa';
			$api_key = 'yt0gtxAuAfZDItcull3vLJG7qjGPyjnN';
			$secret = 'Vszs8LA7LD0sec78';

			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json', 
				),
				'body' => array(),
			);
			$url = "https://api.nytimes.com/svc/topstories/v2/home.json?api-key=$api_key";
			
			$response = wp_remote_get( $url, $args );
			$response_code = wp_remote_retrieve_response_code( $response );
			
			var_dump($response);
	
			// echo "hello";

			$body = wp_remote_retrieve_body($response);

			if (401 === $response_code) {
				return "Unauthorized access";
			}
			if (200 !== $response_code) {
				return "Error Pinging API";
			}
			if (200 === $response_code) {
				return $body;
			}

		}
		

		function create_database_table() {
			global $jh_nyt_table_version;
			global $wpdb;
	
			$jh_nyt_table_version = '1.0.0';
	
			$table_name = $wpdb->prefix . 'jh_nyt_table_version';
	
			$charset_collate = $wpdb->get_charset_collate();
	
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				title text NOT NULL,
				abstract text NOT NULL,
				published_date text NOT NULL,
				urlStory text NOT NULL,
				byline text NOT NULL,
				section text NOT NULL,
				des_facet text NOT NULL,
				PRIMARY KEY  (id)
			  ) $charset_collate;";
	
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
	
			add_option( 'jh_nyt_table_version', $jh_nyt_table_version);
		}

		function save_database_table_info() {
			
			global $wpdb;

			$table_name = $wpdb->prefix . 'jh_nyt_table_version';
			
			$results = json_decode( get_option( 'jh_nyt_top_stories' ) )->results;

			foreach( $results as $result) {
				$wpdb->insert( 
					$table_name, 
					array( 
						'time' => current_time( 'mysql' ), 
						'title' => $result -> title,
						'abstract' => $result -> abstract,
						'published_date' => $result -> published_date,
						'urlStory' => $result -> url,
						'byline' => $result -> byline,
						'section' => $result -> section,
						'des_facet' => $result -> des_facet,
					) 
				);
		
			}

			echo '<pre>';
			var_dump( $results );
			echo '</pre>';
		
		}
	}
}

new NytTopStories;