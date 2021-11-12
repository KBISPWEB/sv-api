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

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		// date_default_timezone_set('America/New_York');

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

		function init_bulk_listings(){
			$post_meta_fields = array(
				'listing_id',
				'address',
				'admissions_info',
				'alternate',
				'category',
				'company',
				'contact',
				'dont_miss',
				'email',
				'facebook',
				'fax',
				'hours',
				'instagram',
				'map_coordinates',
				'phone',
				'rank',
				'region',
				'ticket_information',
				'ticket_link',
				'type_of_member',
				'search_keywords',
				'sort_company',
				'subcategory',
				'tollfree',
				'twitter',
				'wct_id',
				'website',
				'what_its_like',
				'youtube',
			);

			update_option( 'sv_api_listings_added', 0 );
			update_option( 'sv_api_listings_updated',  '' );
			$results_num = sv_api_connection('getListings', 1);
			if( $results_num == 'error'):
				exit();
			endif;
			$results_count = $results_num['REQUESTSTATUS']['RESULTS']; // total listings on SV CRM
			$api_pagesize = 50; // max allowed by Simpleview API
			$api_pagesize = 1; //TODO REMOVE

			$num_calls = ceil($results_count / 50);
			// $num_calls = 25; // TODO REMOVE


			for ($page=1; $page <= $num_calls ; $page++):
				// import_bulk_listings($api_pagesize, $page, $num_calls);
			// }

			// function import_bulk_listings($api_pagesize, $api_pagenum = 1, $num_calls ){
				$hasMore = $page == $num_calls ? false : true;

				$existing_listing_ids = existing_listing_ids();
				$existing_companies = existing_companies();

				$response = sv_api_connection('getListings', $api_pagesize, $page);
				if( $response == 'error'):
					exit();
				endif;
				$listings = $response['LISTINGS']['LISTING'];

				// echo '<hr /><pre style="white-space: pre-wrap;">'; print_r($listings); echo '</pre>';

				$added_count = 0;
				$updated_posts = array();

				foreach ($listings as $listing):
					$last_updated 					= $listing['LASTUPDATED'];

					// if listing ID & Company do not already exist, create new listing
					$listing_id 						= $listing['LISTINGID'];
					$company 								= !empty( $listing['COMPANY'] ) ? $listing['COMPANY'] : 'Company Name Missing';

					// Listing data to compare with current data
					$listing_data 					= array();

					// Get images for this listing
					$image_list = get_images($listing_id);

					// if( !in_array($listing_id, $existing_listing_ids) && !in_array($company, $existing_companies) ):
					// 	array_push($existing_listing_ids, $listing_id);
					// 	array_push($existing_companies, $company);

						$address 								= '';
						$address 								.= !empty( $listing['ADDR1'] ) ? $listing['ADDR1'] : '';
						$address 								.= !empty( $listing['ADDR2'] ) ? "\n" . $listing['ADDR2'] : '';
						$address 								.= !empty( $listing['ADDR3'] ) ? "\n" . $listing['ADDR3'] : '';
						$city 									= !empty( $listing['CITY'] ) ? $listing['CITY'] : '';
						$state 									= !empty( $listing['STATE'] ) ? $listing['STATE'] : '';
						$zip 										= !empty( $listing['ZIP'] ) ? ' ' . $listing['ZIP'] : '';
							if( $city != '' && $state != ''):
								$city_state = $city . ', ' . $state;
							else:
								$city_state = $city . $state;
							endif;
		 				$address 								.= "\n" . $city_state . $zip;

						$map_coordinates 				= '';
						if( !empty( $listing['LATITUDE']) && !empty( $listing['LONGITUDE'] ) ):
							$map_coordinates 				= $listing['LATITUDE'] . ',' . $listing['LONGITUDE'];
						endif;

						$phone 									= !empty( $listing['PHONE'] ) ? $listing['PHONE'] : '';
						$alternate 							= !empty( $listing['ALTPHONE'] ) ? $listing['ALTPHONE'] : '';
						$tollfree 							= !empty( $listing['TOLLFREE'] ) ? $listing['TOLLFREE'] : '';
						$fax 										= !empty( $listing['FAX'] ) ? $listing['FAX'] : '';

						$sort_company 					= !empty( $listing['SORTCOMPANY'] ) ? $listing['SORTCOMPANY'] : $listing_id . ' Company Name Missing';
						$contact 								= !empty( $listing['PRIMARYCONTACTFULLNAME'] ) ? $listing['PRIMARYCONTACTFULLNAME'] : '';
						$email 									= !empty( $listing['EMAIL'] ) ? $listing['EMAIL'] : '';
						$hours 									= !empty( $listing['HOURS'] ) ? $listing['HOURS'] : '';
						$rank 									= !empty( $listing['RANKNAME'] ) ? $listing['RANKNAME'] : '';
						$region 								= !empty( $listing['REGION'] ) ? $listing['REGION'] : '';
						$admissions_info 				= !empty( $listing['ADMISSIONSINFORMATIONBLOCK'] ) ? $listing['ADMISSIONSINFORMATIONBLOCK'] : '';
						$search_keywords 				= !empty( $listing['LISTING_KEYWORDS'] ) ? $listing['LISTING_KEYWORDS'] : '';
						$ticket_information 		= !empty( $listing['TICKETINFORMATION'] ) ? $listing['TICKETINFORMATION'] : '';
						$ticket_link 						= !empty( $listing['TICKETSLINK'] ) ? $listing['TICKETSLINK'] : '';
						$type_of_member 				= !empty( $listing['TYPEOFMEMBER'] ) ? $listing['TYPEOFMEMBER'] : '';
						$wct_id 								= !empty( $listing['WCTID'] ) ? $listing['WCTID'] : '';
						$website 								= !empty( $listing['WEBURL'] ) ? $listing['WEBURL'] : '';

						$description 						= !empty( $listing['DESCRIPTION'] ) ? $listing['DESCRIPTION'] : '';

						$what_its_like 					= !empty( $listing['WHATSITLIKEINFORMATIONBLOCK'] ) ? $listing['WHATSITLIKEINFORMATIONBLOCK'] : '';
						$dont_miss 							= !empty( $listing['DONTMISSINFORMATIONBLOCK'] ) ? $listing['DONTMISSINFORMATIONBLOCK'] : '';

						$twitter 								= '';
						$facebook 							= '';
						$instagram 							= '';
						$youtube 								= '';


						$social_media = !empty( $listing['SOCIALMEDIA']['ITEM'] ) ? $listing['SOCIALMEDIA']['ITEM'] : '';
						// Handle social media URLS.
						// SV API Arrays differ if there is only 1 SOCIALMEDIA ITEM
						// Multiple items
						if( is_array( $social_media[0] ) ):
							foreach ($social_media as $network):
								$network_service = strtolower( $network['SERVICE'] );
								$$network_service = !empty( $network['VALUE'] ) ? $network['VALUE'] : ''; // variable variable
							endforeach;
						// Only 1 item
						else:
							$network_service = strtolower( $social_media['SERVICE'] );
							$$network_service = !empty( $social_media['VALUE'] ) ? $social_media['VALUE'] : ''; // variable variable
						endif;

						// handle categories
						$post_cats = array();
						$cat_name 							= !empty( $listing['CATNAME'] ) ? $listing['CATNAME'] : '';
						$cat_slug								= reformCategorySlug($cat_name);
						$category 							= addCategory($cat_name, $cat_slug);

						$subcat_name 					 	= !empty( $listing['SUBCATNAME'] ) ? $listing['SUBCATNAME'] : '';
						if( $subcat_name != ''):
							$subcat_slug						= reformCategorySlug($subcat_name);
							$subcategory 						= addCategory($subcat_name, $subcat_slug, $category);
						endif;
						array_push($post_cats, $category, $subcategory);

						// In current field list, but not in API
						$address_type 					= '';

						// New Fields that aren't available yet
						$hero_image 						= '';
						$what_its_like_image_gallery = '';


						// TODO REMOVE
						// if ($listing_id == '15051'):
						// 	$sort_company = 'Test Company';
						// 	$contact = 'Bob';
						// elseif ($listing_id == '4567'):
						// 	$sort_company = '17 Light';
						// 	$contact = 'Greg';
						// endif;
						// END TODO REMOVE

						// TODO REMOVE????
						$listing_data['post_content'] = $description;
						$listing_data['post_title'] = $sort_company;
						foreach ($post_meta_fields as $value) {
							$listing_data[$value] = $$value;
						}
						// END TODO REMOVE????

					// Add new listings (only add type of website)
					if( !in_array($listing_id, $existing_listing_ids) && !in_array($company, $existing_companies) && ($type_name == "Website")  ):
						array_push($existing_listing_ids, $listing_id);
						array_push($existing_companies, $company);

						// Create the post
						$post = array(
							'post_title'    => $sort_company,
							'post_author'   => 1,
							'post_content'  => $description,
							'post_status'   => 'publish',
							'post_type'     => 'listings'
						);
						$pid = wp_insert_post($post, true);  // Pass the value of $post to WordPress the insert function

						// add post terms
						wp_set_post_terms($pid, $post_cats, 'category');

						// populate the post meta data
						foreach ($post_meta_fields as $value) {
							update_post_meta($pid, $value, $$value);
						}

						$added_count++;

						// Add images to listing media gallery
						handle_images($image_list, $pid, $sort_company);

					// Listing ID exists, check last_updated & update if needed
					elseif( in_array($listing_id, $existing_listing_ids) ):
						// echo $last_updated . '<hr />';
						// echo '<hr />' . $listing_id . '<br />';

						// Get post data
						$post_data = get_posts( [
							'post_type' => 'listings',
					    'meta_key'   => 'listing_id',
					    'meta_value' => $listing_id,
							'posts_per_page' => 1,
							// 'fields' => 'ids'
						] );
						// echo '<pre style="white-space: pre-wrap;">' . $q->request . '</pre>';
						// echo '<pre style="white-space: pre-wrap;">'; print_r($post_data); echo '</pre>';

						// Set current post ID
						$pid 						= $post_data[0]->ID;
						// Get latest modification date
						$post_modified 	= $post_data[0]->post_modified;

						// Check if needs updated
						if( $last_updated > $post_modified ):
							// echo 'NEEDS UPDATE<br />' . $last_updated . ' | ' . $post_modified . '<br />';

							$time = current_time('mysql');
							$post_updates = array(
								'ID' 						=> $pid,
								'post_title'    => $sort_company,
								'post_content'  => $description,
								'post_modified'     => $time,
				        'post_modified_gmt' => get_gmt_from_date( $time ),
							);
							$pid = wp_update_post($post_updates, true);

							// populate the post meta data
							foreach ($post_meta_fields as $value) {
								update_post_meta($pid, $value, $$value);
							}
							$updated_posts[$pid] = $sort_company;

							// Add images to listing media gallery
							handle_images($image_list, $pid, $sort_company);
						endif;

					endif;
				endforeach; // foreach $listings
				// echo '<hr />'; // TODO Remove
				update_option( 'sv_api_listings_added', get_option( 'sv_api_listings_added' ) + $added_count );
				$existing_updates = get_option( 'sv_api_listings_updated' );
				array_merge($updated_posts, $existing_updates);
				update_option( 'sv_api_listings_updated',  $updated_posts );


				$data = array(
						// 'page'    => $page,
						'hasMore' => $hasMore,
						// 'logData' => $html,
						// 'total'   => $total,
						// 'paged'   => $paged,
						// 'count'   => $count,
						// 'failed'  => $failed,
						// 'resultCheck' => $resultCheckArr,
						// 'percent' => round(($paged / $num_calls) * 100,2)
				);
			endfor;
			if(!$hasMore){
				init_bulk_events($data);
			}
		} // init_bulk_listings

		// EVENTS CONNECTION
		function sv_events_api_connection() {
			$haserrors = false;

			$options = get_option('sv_api_setting_options');
			$events_api_url = rtrim( $options['events_api_url'], '/' ) . '/';
			$events_api_key = $options['events_api_key'];

			$events_api_url .= '?apikey=';
			// $events_api_url .= 'feeds/events.cfm?apikey';
			$events_api_url .= $events_api_key;

			// $events_api_xml_content = file_get_contents($events_api_url);
			$events_content = simplexml_load_file($events_api_url);

			$haserrors = $events_content->success == 'Yes' ? false : true;

			if($haserrors):
				update_option( 'sv_api_events_failure_message', $events_content->message );
				update_option( 'sv_api_events_failure', true );
				return 'error';
			else:
				update_option( 'sv_api_events_failure', false );

				$eventsWrapper = (array)$events_content->events;
				$eventsArray = $eventsWrapper['event'];

				update_option( 'sv_api_events_results_count', count($eventsArray) );
				return $eventsArray;
			endif;
		} //sv_events_api_connection

		function init_bulk_events(){
			$events = sv_events_api_connection();
			// return $events;
			if($events == 'error'):
				exit();
			endif;

			$post_meta_fields = array(
				'address',
				'admission',
				'city',
				'contact',
				'created',
				'customfields',
				'email',
				'enddate',
				'endtime',
				'eventdates',
				'eventid',
				'eventregion',
				'eventtype',
				'featured',
				'hostlistingid',
				'hostname',
				'lastupdated',
				'listingid',
				'location',
				'map_coordinates',
				'mediafile',
				'neverexpire',
				'phone',
				'recurrence',
				'startdate',
				'starttime',
				'state',
				'times',
				'website',
				'zip',
			);

			update_option( 'sv_api_events_added', 0 );
			$existing_event_ids = existing_event_ids();

			$added_count = 0;
			$updated_posts = array();
			foreach($events as $event):
				$description						= !empty( strval($event->event->description) ) ? strval($event->event->description) : '';
				$title 									= !empty( strval($event->event->title) ) ? strval($event->event->title) : '';

				$address 								= !empty( strval($event->event->address) ) ? strval($event->event->address) : '';
				$admission 							= !empty( strval($event->event->admission) ) ? strval($event->event->admission) : '';
				$city 									= !empty( strval($event->event->city) ) ? strval($event->event->city) : '';
				$contact 								= !empty( strval($event->event->contact) ) ? strval($event->event->contact) : '';
				$created 								= !empty( strval($event->event->created) ) ? strval($event->event->created) : '';

				// handle $customfields
				$customfields_array     = array();
				$event_customfields  		= $event->event->customfields;
				foreach ($event_customfields as $customfield):
					array_push( $customfields_array, strval($customfield->customfield->name) );
				endforeach;
				$customfields = implode(",", $customfields_array);

				$email 									= !empty( strval($event->event->email) ) ? strval($event->event->email) : '';
				$enddate 								= !empty( strval($event->event->enddate) ) ? strval($event->event->enddate) : '';
				$endtime 								= !empty( strval($event->event->endtime) ) ? strval($event->event->endtime) : '';

				// handle $eventdates
				$eventdates_array     	= array();
				$event_eventdates  			= $event->event->eventdates;
				foreach ($event_eventdates as $eventdate):
					array_push($eventdates_array, strval($eventdate->eventdate) );
				endforeach;
				$eventdates 						= implode(",", $eventdates_array);

				$eventid 								= !empty( strval($event->event->eventid) ) ? strval($event->event->eventid) : '';
				$eventregion 						= !empty( strval($event->event->eventregion) ) ? strval($event->event->eventregion) : '';
				$eventtype 							= !empty( strval($event->event->eventtype) ) ? strval($event->event->eventtype) : '';
				$featured 							= !empty( strval($event->event->featured) ) ? strval($event->event->featured) : '';
				$hostlistingid 					= !empty( strval($event->event->hostlistingid) ) ? strval($event->event->hostlistingid) : '';
				$hostname 							= !empty( strval($event->event->hostname) ) ? strval($event->event->hostname) : '';
				$lastupdated 						= !empty( strval($event->event->lastupdated) ) ? strval($event->event->lastupdated) : '';
				$listingid 							= !empty( strval($event->event->listingid) ) ? strval($event->event->listingid) : '';
				$location 							= !empty( strval($event->event->location) ) ? strval($event->event->location) : '';

				$map_coordinates 				= '';
				if( !empty( $event->event->latitude ) && !empty( $event->event->longitude ) ):
					$map_coordinates 				= strval($event->event->latitude) . ',' . strval($event->event->longitude);
				endif;

				$mediafile 							= '';

				$neverexpire 						= !empty(strval( $event->event->neverexpire) ) ? strval($event->event->neverexpire) : '';
				$phone 									= !empty(strval( $event->event->phone) ) ? strval($event->event->phone) : '';
				$recurrence 						= !empty(strval( $event->event->recurrence) ) ? strval($event->event->recurrence) : '';
				$startdate 							= !empty(strval( $event->event->startdate) ) ? strval($event->event->startdate) : '';
				$starttime 							= !empty(strval( $event->event->starttime) ) ? strval($event->event->starttime) : '';
				$state 									= !empty(strval( $event->event->state) ) ? strval($event->event->state) : '';
				$times 									= !empty(strval( $event->event->times) ) ? strval($event->event->times) : '';
				$website 								= !empty(strval( $event->event->website) ) ? strval($event->event->website) : '';
				$zip 										= !empty(strval( $event->event->zip) ) ? strval($event->event->zip) : '';

				$featured_image 				= !empty(strval( $event->event->imagefile) ) ? strval($event->event->imagefile) : '';

				$image_list 						=  array();
				$event_images 					= $event->event->images->image;
				foreach($event_images as $images):
					$image_list[] = strval($images->mediafile);
				endforeach;

				// handle categories
				$post_cats = array();
				$eventcategories				= !empty($event->event->eventcategories) ? $event->event->eventcategories : '';
				foreach ($eventcategories->eventcategory as $category):
					$cat_name 							= strval($category->categoryname);
					$cat_slug								= reformCategorySlug($cat_name);
					$category 							= addCategory($cat_name, $cat_slug);
					array_push( $post_cats, $category );
				endforeach;
				// echo '<hr /><pre style="white-space: pre-wrap;">'; print_r($post_cats); echo '</pre>';

				// Add new event
				if( !in_array($eventid, $existing_event_ids) ):
					array_push($existing_event_ids, $eventid);

					// Create the event post
					$post = array(
						'post_title'    => $title,
						'post_author'   => 1,
						'post_content'  => $description,
						'post_status'   => 'publish',
						'post_type'     => 'events'
					);
					$pid = wp_insert_post($post, true);  // Pass the value of $post to WordPress the insert function

					// add post terms
					wp_set_post_terms($pid, $post_cats, 'category');

					// populate the post meta data
					foreach ($post_meta_fields as $value):
						update_post_meta($pid, $value, $$value);
					endforeach;

					// add featured image
					$featured_id = saveImageToWP($featured_image, $pid, $title);
					set_post_thumbnail($pid, $featured_id);

					$added_count++;
					// Add images to listing media gallery
					handle_images($image_list, $pid, $title);

				endif;
			endforeach;
			update_option( 'sv_api_events_added', get_option( 'sv_api_events_added' ) + $added_count );

			wp_send_json($data);
		} // init_bulk_events

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

				/*
				if ($hero[0] != true) {

					if ($hero_image !== false) {
						update_post_meta($pid, 'post_hero_background', $hero_image);
					}
					elseif ($backup_hero_image !== false) {
						update_post_meta($pid, 'post_hero_background', $backup_hero_image);
					}
					elseif ( isset($gallery[0][0]) ) { //fallback to 1st gallery
						if ($gallery[0][0]) {
							update_post_meta($pid, 'post_hero_background', $$gallery[0][0]);
						}
					}
				}
				*/

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

					// If error storing temporarily, unlink
					if ( is_wp_error( $tmp ) ) {
						//var_dump($tmp);
						@unlink($file_array['tmp_name']);
						$file_array['tmp_name'] = '';
					}

					// do the validation and storage stuff
					$mid = media_handle_sideload( $file_array, $pid, $desc );

					// If error storing permanently, unlink
					if ( is_wp_error($mid) ) {
						@unlink($file_array['tmp_name']);
					}
					return $mid;
			}
		} // saveImageToWP


	}
}
