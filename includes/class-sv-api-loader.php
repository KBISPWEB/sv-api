<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://bellweather.agency/
 * @since      1.0.0
 *
 * @package    SV_Api
 * @subpackage SV_Api/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    SV_Api
 * @subpackage SV_Api/includes
 * @author     Bellweather Agency <dan@bellweather.agency>
 */
class SV_Api_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

		// Need to require these files
		if ( !function_exists('media_handle_sideload') ) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		// TODO: maybe this is a good place to run the introspection of the api

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		// TODO: find a better place to put these helper functions

		function handle_array_anomalies($images){
			if (isset($images['MEDIAID'])) { //this is the bug
				$wrapper[0] = $images;
				return $wrapper;
			}
			else {
				return $images;
			}
		}

		function get_all_current_listings(){
			$post_stati = get_post_stati();
			unset($post_stati['trash']); // remove trash from post_status search

			$get_existing_listing_ids = new WP_Query(array(
				'post_type' => 'listings',
				'post_status' => $post_stati,
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => 'listing_id',
						'compare' => 'EXISTS',
					),
				),
			));
			return $get_existing_listing_ids->posts;
		} // get_all_current_listings

		function get_all_current_events(){
			$post_stati = get_post_stati();
			unset($post_stati['trash']); // remove trash from post_status search

			$get_existing_event_ids = new WP_Query(array(
				'post_type' => 'events',
				'post_status' => $post_stati,
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'     => 'eventid',
						'compare' => 'EXISTS',
					),
				),
			));
			return $get_existing_event_ids->posts;
		} // get_all_current_events

		function addCategory($cat_name, $cat_slug, $parent = '', $tax = 'category') {
			require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
			$check_cat_exist = get_category_by_slug($cat_slug);
			if( $check_cat_exist == false ):
				$cat_array = array(
					'cat_name' => $cat_name,
					'category_description' => '',
					'category_nicename' => $cat_slug,
					'category_parent' => $parent,
					'taxonomy' => $tax,
				);
				$cat_id = wp_insert_category($cat_array);
			else:
				$cat_id = $check_cat_exist->term_id;
			endif;
			return $cat_id;
		} // addCategory

		function existing_companies(){
			$companies = get_all_current_listings();
			$existing_companies = array();
			foreach($companies as $company) {
					if( !in_array($company->company, $existing_companies) ):
						array_push($existing_companies, $company->company);
					endif;
			}
			// return $get_existing_listing_ids->request;
			return $existing_companies;
		} // existing_companies

		function existing_listing_ids(){
				$listings = get_all_current_listings();
				$existing_listing_ids = array();
				foreach($listings as $listing) {
				    if( !in_array($listing->listing_id, $existing_listing_ids) ):
							array_push($existing_listing_ids, $listing->listing_id);
						endif;
				}
				return $existing_listing_ids;
		} // existing_listing_ids

		function existing_event_ids(){
				$events = get_all_current_events();
				$existing_event_ids = array();
				foreach($events as $event) {
				  if( !in_array($event->eventid, $existing_event_ids) ):
						array_push($existing_event_ids, $event->eventid);
					endif;
				}
				return $existing_event_ids;
		} // existing_event_ids

		function check_listings_settings() {
			$verdict = true;
			$options = get_option('sv_api_setting_options');
			$settings = array( 'api_url', 'api_username', 'api_password' );

			foreach ($settings as $value):
				if( $options[$value] == '' ):
					$verdict = false;
					break;
				endif;
			endforeach;

			return $verdict;

		} // check_listings_settings


		function check_events_settings() {
			$verdict = true;
			$options = get_option('sv_api_setting_options');
			$settings = array( 'events_api_url', 'events_api_key' );

			foreach ($settings as $value):
				if( $options[$value] == '' ):
					$verdict = false;
					break;
				endif;
			endforeach;

			return $verdict;

		} // check_events_settings

		// Update Premium Listings Meta
		function update_premium_meta($pid, $premium_response) {
			$listing_type_id_arr = term_exists( 'premium', 'listing_type' );
			$listing_type_id = $listing_type_id_arr['term_id'];
			wp_set_post_terms( $pid, array( $listing_type_id ), 'listing_type' );

			$hours = $premium_response['Hours'];
			if (!is_array($hours)) {
				update_field("hours", $hours, $pid);
			}
			else {
				update_field("hours", "", $pid);
			}

			$ticket_information = $premium_response['TicketInformation'];
			if (!is_array($ticket_information)) {
				update_field("ticket_information", $ticket_information, $pid);
			}
			else {
				update_field("ticket_information", "", $pid);
			}

			$ticket_link = $premium_response['TicketsLink'];
			if (!is_array($ticket_link)) {
				update_field("ticket_link", $ticket_link, $pid);
			}
			else {
				update_field("ticket_link", "", $pid);
			}

			$admissions_info = $premium_response['AdmissionsInformationBlock'];
			if (!is_array($admissions_info)) {
				update_field("admissions_info", $admissions_info, $pid);
			}
			else {
				update_field("admissions_info", "", $pid);
			}

			$what_its_like = $premium_response['WhatsItLikeInformationBlock'];
			if (!is_array($what_its_like)) {
				update_field("what_its_like", $what_its_like, $pid);
			}
			else {
				update_field("what_its_like", "", $pid);
			}

			$dont_miss = $premium_response['DontMissInformationBlock'];
			if (!is_array($dont_miss)) {
				update_field("dont_miss", $dont_miss, $pid);
			}
			else {
				update_field("dont_miss", "", $pid);
			}
		}

		// LISTING CONNECTION
		function sv_api_connection( $api_action = 'getListings', $api_pagesize = 50, $api_pagenum = 1, $listing_id = '', $hit_data=false ){
			$options = get_option('sv_api_setting_options');
			// remove trailing / if there needed & add / to ensure trailing / exists
			$api_url = rtrim( $options['api_url'], '/' ) . '/';
			// $api_url = '';
			$api_url .= 'webapi/listings/xml/listings.cfm';

			$api_username = $options['api_username'];
			$api_password = $options['api_password'];

			$haserrors = false;
			$no_listing_id = false;

			$data = 'Username=' . $api_username;
			$data .= '&Password=' . $api_password;
			$data .= '&Action=' . $api_action;
			if( $api_action == 'getListings' ):
				$data .= '&Pagesize=' . $api_pagesize;
				$data .= '&Pagenum=' . $api_pagenum;
				$data .= '&Displayamenities=1';
			elseif( $api_action == 'getListing' ):
				if( $listing_id != '' ):
					$data .= '&ListingID=' . $listing_id;
					$data .= '&updateHits=0';
				else:
					$no_listing_id = true;
				endif;
			elseif( $api_action == 'getCoupons'):
				$data .= '&Pagesize=' . $api_pagesize;
				$data .= '&Pagenum=' . $api_pagenum;
			endif;
			// $data = 'Username=' . $api_username . '&Password=' . $api_password . '&Action=' . $api_action . '&Pagesize=50&Pagenum=1&Displayamenities=1';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_URL, $api_url);
			$result = curl_exec($ch);
			$result_info = curl_getinfo($ch);
			curl_close($ch);

			// echo '<input type="textarea" value="' . htmlspecialchars($result) . '">';

			$result_http_code = $result_info['http_code'];

			$xml = simplexml_load_string($result);
			$json = json_encode($xml);
			$response = json_decode($json,TRUE);

			if( $api_action == 'getListings' ):
				$haserrors = $response['REQUESTSTATUS']['HASERRORS'];
				$results_count = $response['REQUESTSTATUS']['RESULTS'];
			endif;

			//results_info
			//has_errors

			// return $result;

			if($haserrors || $result_http_code != '200' ):
				if( $haserrors ):
					update_option( 'sv_api_failure_message', $response['REQUESTSTATUS']['ERRORS']['ITEM']['MESSAGE'] );
					if( !empty( $response['REQUESTSTATUS']['ERRORS']['ITEM']['DETAIL'] ) ):
						update_option( 'sv_api_failure_detail', $response['REQUESTSTATUS']['ERRORS']['ITEM']['DETAIL'] );
					endif;
				else:
					update_option( 'sv_api_failure_message', 'http_code' );
					update_option( 'sv_api_failure_detail', $result_http_code );
				endif;
				update_option( 'sv_api_failure', 'yes' );
				return 'error';
			elseif ( $no_listing_id ):
				update_option( 'sv_api_failure_message', 'http_code' );
				update_option( 'sv_api_failure_detail', $result_http_code );
				update_option( 'sv_api_failure', 'yes' );
			else:
				update_option( 'sv_api_failure_message', 'No Failure.' );
				update_option( 'sv_api_failure', 'no' );
				if( $api_action == 'getListings' ):
					update_option( 'sv_api_results_count', $results_count );
				endif;
				return $response;
			endif;
		} // sv_api_connection

		// EVENTS CONNECTION
		function sv_events_api_connection() {
			$haserrors = false;

			$options = get_option('sv_api_setting_options');
			$events_api_url = rtrim( $options['events_api_url'], '/' ) . '/';
			$events_api_key = $options['events_api_key'];

			$events_api_url .= '?apikey=';
			// $events_api_url .= 'feeds/events.cfm?apikey';
			$events_api_url .= $events_api_key;

			$data = 'Length=1000';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_URL, $events_api_url);
			$result = curl_exec($ch);
			$result_info = curl_getinfo($ch);
			curl_close($ch);

			$resultNoApostrophe = str_replace("&#x92;", "'", $result);
			$xmlArray = XMLtoArray($resultNoApostrophe);
			$response = $xmlArray['RESULTS'];

			$haserrors = $response['SUCCESS'] == 'Yes' ? false : true;
			if($haserrors):
				update_option( 'sv_api_events_failure_message', isset($response['MESSAGE']) ? 'No Failure Specified' : $response['MESSAGE'] );
				update_option( 'sv_api_events_failure', true );
				return 'error';
			else:
				update_option( 'sv_api_events_failure', false );
				
				$eventsArray = $response['EVENTS']['EVENT'];

				update_option( 'baltimore_crm_api_events_results_count', count($eventsArray) );
				return $eventsArray;
			endif;

		} //sv_events_api_connection

		function reformCategorySlug($string) {
			//Lower case everything
			$string = strtolower($string);
			//Make alphanumeric (removes all other characters)
			$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
			//Clean up multiple dashes or whitespaces
			$string = preg_replace("/[\s-]+/", " ", $string);
			//Convert whitespaces and underscore to dash
			$string = preg_replace("/[\s_]/", "-", $string);
			return $string;
		} // reformCategorySlug

		function get_images($listing_id){
			$response = sv_api_connection('getListing', 0, 0, $listing_id);

			$images_list = array();

			$images = array();
			$images = $response['LISTING']['IMAGES']['ITEM'];

			if( is_array( $images[0] ) ):
				foreach ($images as $image):
					$images_list[] = $image['IMGPATH'] . $image['MEDIAFILE'];
				endforeach;
			else:
				$images_list[] = $images['IMGPATH'] . $images['MEDIAFILE'];
			endif;

			return $images_list;
		} // get_images

		function handle_images($image_list = null, $pid = null, $sort_company = '', $meta_key = 'media'){
			if($image_list && $pid):

				$mid = $meta_key == 'media' ? array() : '';

				$index_for_image_list = 1;
				foreach ($image_list as $image_url) {
					$id = saveImageToWP($image_url, $pid, $sort_company);
					if($meta_key == 'media'){
						array_push($mid, $id);
					}
					else{
						$mid = $id;
					}
				}
			endif;

			update_post_meta($pid, $meta_key, $mid);
		} // handle_images

		function BW_handle_images($image_list = null, $pid = null, $sort_company = ''){
			if($image_list && $pid):

				$index_for_image_list = 1;
				$added_featured = false;
				foreach ($image_list as $image_url) {

					$id = saveImageToWP($image_url, $pid, $sort_company);

					if (!$added_featured) { // set first image to be thumbnail
						set_post_thumbnail($pid, $id);
						$added_featured = true;
					}

					array_push($mid, $id);
				}
			endif;

			update_post_meta($pid, 'media', $mid);
		} // handle_images

		function BW_upload_images($pid = null, $images = null, $premium_images = null) {
			if($pid):

				$gallery 		= get_post_meta($pid, 'media');
				$gallery_ids 	= isset($gallery[0]) ? $gallery[0] : [];
				$gallery_sv_ids = array();

				foreach ($gallery_ids as $id) {
					$gallery_sv_ids[] = get_field('simpleview_id', $id);
				}

				$ids_to_apply = array();

				$hero_image = false;
				$backup_hero_image = false;

				// HANDLE IMAGES
				if ($images) {
					foreach ($images as $sv_id => $image) {
						$id_index = array_search($sv_id, $gallery_sv_ids);
						if ( in_array($sv_id, $gallery_sv_ids) ) {
							$id = $gallery_ids[$id_index];
							$ids_to_apply[] = $id;
						}
						else { // this is a new image
							$id = saveImageToWP($image['URL'], $pid, $image['TITLE']);
							update_field('simpleview_id', $sv_id, $id);
							$ids_to_apply[] = $id;
						}
						if ($backup_hero_image === false) {
							$backup_hero_image = $id;
						}
					}
				}

				// HANDLE PREMIUM IMAGES
				if ($premium_images) {
					foreach ($premium_images as $sv_id => $image) {
						$id_index = array_search($sv_id, $gallery_sv_ids);
						if ( in_array($sv_id, $gallery_sv_ids) ) {
							$id = $gallery_ids[$id_index];
							$ids_to_apply[] = $id;
						}
						else { // this is a new image
							$id = saveImageToWP($image['URL'], $pid, $image['TITLE']);
							update_field('simpleview_id', $sv_id, $id);
							update_post_meta($id, '_wp_attachment_image_alt', $image['DESC']);
							$ids_to_apply[] = $id;
						}
						if ($hero_image === false) {
							$hero_image = $id;
						}
					}
				}

				update_post_meta($pid, 'media', $ids_to_apply); 	// add to gallery


				// HANDLE HERO

				$hero = get_post_meta($pid, 'post_hero_background');

				if( !has_post_thumbnail($pid) ){
					if ($hero_image !== false) {
						set_post_thumbnail($pid, $hero_image);
					}
					elseif ($backup_hero_image !== false) {
						set_post_thumbnail($pid, $backup_hero_image);
					}
					elseif ( isset($gallery[0][0]) ) { //fallback to 1st gallery
						if ($gallery[0][0]) {
							set_post_thumbnail($pid, $gallery[0][0]);
						}
					}
				}

			endif;
		} // BW_upload_images

		function saveImageToWP($image_url = null, $pid = null, $desc = 'null', $append = '') {
			// Get image from external url and save to Media Library
			if($image_url && $pid) {

					$tmp = download_url( $image_url );

					if ( is_wp_error($tmp) ) {
						return false;
					}

					// $desc = get_the_title($pid);
					$file_array = array();

					// Set variables for storage
					// fix file filename for query strings
					preg_match('/[^?]+.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches);
					$str_to_splice = isset($matches[0]) ? basename($matches[0]) : '';

					$str_pos = strpos($str_to_splice, ".");

					$str1 = substr($str_to_splice, 0, $str_pos);
					$str2 = substr($str_to_splice, $str_pos);

					$file_array['name'] = $str1.$append.$str2;
					$file_array['tmp_name'] = $tmp;

					// do the validation and storage stuff
					$mid = media_handle_sideload( $file_array, $pid, $desc );

					if ( is_wp_error($mid) ) {
						return false;
					}
					return $mid;
			}
		} // saveImageToWP


		/**
		 * Convert XML to an Array
		 *
		 * @param string  $XML
		 * @return array
		 */
		function XMLtoArray($XML)
		{
			$xml_parser = xml_parser_create();
			xml_parse_into_struct($xml_parser, $XML, $vals);
			xml_parser_free($xml_parser);

			$_tmp='';
			foreach ($vals as $xml_elem) {
				$x_tag=$xml_elem['tag'];
				$x_level=$xml_elem['level'];
				$x_type=$xml_elem['type'];
				if ($x_level!=1 && $x_type == 'close') {
					if (isset($multi_key[$x_tag][$x_level]))
						$multi_key[$x_tag][$x_level]=1;
					else
						$multi_key[$x_tag][$x_level]=0;
				}
				if ($x_level!=1 && $x_type == 'complete') {
					if ($_tmp==$x_tag)
						$multi_key[$x_tag][$x_level]=1;
					$_tmp=$x_tag;
				}
			}
			// jedziemy po tablicy
			foreach ($vals as $xml_elem) {
				$x_tag=$xml_elem['tag'];
				$x_level=$xml_elem['level'];
				$x_type=$xml_elem['type'];
				if ($x_type == 'open')
					$level[$x_level] = $x_tag;
				$start_level = 1;
				$php_stmt = '$xml_array';
				if ($x_type=='close' && $x_level!=1)
					$multi_key[$x_tag][$x_level]++;
				while ($start_level < $x_level) {
					$php_stmt .= '[$level['.$start_level.']]';
					if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
						$php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
					$start_level++;
				}
				$add='';
				if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type=='open' || $x_type=='complete')) {
					if (!isset($multi_key2[$x_tag][$x_level]))
						$multi_key2[$x_tag][$x_level]=0;
					else
						$multi_key2[$x_tag][$x_level]++;
					$add='['.$multi_key2[$x_tag][$x_level].']';
				}
				if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes', $xml_elem)) {
					if ($x_type == 'open')
						$php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
					else
						$php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
					eval($php_stmt_main);
				}
				if (array_key_exists('attributes', $xml_elem)) {
					if (isset($xml_elem['value'])) {
						$php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
						eval($php_stmt_main);
					}
					foreach ($xml_elem['attributes'] as $key=>$value) {
						$php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
						eval($php_stmt_att);
					}
				}
			}
			return $xml_array;
		} // XMLtoArray

	}
}
