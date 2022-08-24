<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://bellweather.agency/
 * @since      1.0.0
 *
 * @package    SV_Api
 * @subpackage SV_Api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SV_Api
 * @subpackage SV_Api/admin
 * @author     Bellweather Agency <dan@bellweather.agency>
 */
class SV_Api_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_dependencies();

	}

	/**
	* Load the required dependencies for the Admin facing functionality.
	*
	* Include the following files that make up the plugin:
	*
	* @since    1.0.0
	* @access   private
	*/
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/class-sv-api-settings.php';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SV_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SV_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sv-api-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SV_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SV_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sv-api-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Add an options page under the Settings submenu
	 *
	 * @since  1.0.0
	 */
	public function add_management_page() {

		$this->plugin_screen_hook_suffix = add_management_page(
			__( 'SV API', 'sv' ),
			__( 'SV API', 'sv' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);

	}

	/**
	 * Render the options page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_options_page() {
		include_once 'partials/sv-api-admin-display.php';
	}

	/**
	 * Adds Custom Post Types
	 * TODO: Allow some user control over what post types are created
	 * @since  1.0.0
	 */
	public function add_cpts() {
		register_post_type(
			'listings',
			array(
			  	'labels' => array(
					'name'                       => _x( 'Listings', 'taxonomy general name', 'textdomain' ),
					'singular_name'              => _x( 'Listing', 'taxonomy singular name', 'textdomain' ),
					'search_items'               => __( 'Search Listings', 'textdomain' ),
					'popular_items'              => __( 'Popular Listings', 'textdomain' ),
					'all_items'                  => __( 'All Listings', 'textdomain' ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Listings', 'textdomain' ),
					'update_item'                => __( 'Update Listings', 'textdomain' ),
					'add_new_item'               => __( 'Add New Listings', 'textdomain' ),
					'new_item_name'              => __( 'New Listings Name', 'textdomain' ),
					'separate_items_with_commas' => __( 'Separate Listings with commas', 'textdomain' ),
					'add_or_remove_items'        => __( 'Add or remove Listings', 'textdomain' ),
					'choose_from_most_used'      => __( 'Choose from the most used Listings', 'textdomain' ),
					'not_found'                  => __( 'No Listings found.', 'textdomain' ),
					'menu_name'                  => __( 'Listings', 'textdomain' ),
			  	),
				'public' => true,
				'has_archive' => true,
				'hierarchical' => true,
				'taxonomies' => array('post_tag', 'category'),
				'menu_icon' => 'dashicons-admin-site-alt3',
				'supports' => array('revisions','editor','title','excerpt','thumbnail'),
				'rewrite' => array( 'slug' => 'listings', 'with_front'=> true )
			)
		);
	}

	/**
	 * Adds ACF fields for Listings
	 *
	 * @since  1.0.0
	 */
	public function add_acf_fields() {
		if( function_exists('acf_add_local_field_group') ):

		// Listing ACF
		acf_add_local_field_group(array (
			'key' => 'group_5e84d585970a8',
			'title' => 'Listing Fields',
			'fields' => array (
				array(
					'key' => 'field_5e84f3dcb0f74',
					'label' => '<span class="dashicons dashicons-list-view"></span> Listing Info',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4d981e4c406',
					'label' => 'Listing ID',
					'name' => 'listing_id',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d991ce405e',
					'label' => 'Company',
					'name' => 'company',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9d5b3e5a9',
					'label' => 'Sort Company',
					'name' => 'sort_company',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array(
					'key' => 'field_5e7112c695fff',
					'label' => 'Featured',
					'name' => 'featured',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => 'Yes',
					'ui_off_text' => 'No',
				),
				array(
					'key' => 'field_5e84d3eec1149',
					'label' => '<span class="dashicons dashicons-id-alt"></span> Contact Info',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4d995a27f47',
					'label' => 'Address',
					'name' => 'address',
					'type' => 'textarea',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 8,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d98f276a60',
					'label' => 'Contact',
					'name' => 'contact',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9e643c28e',
					'label' => 'Email',
					'name' => 'email',
					'type' => 'email',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
				),
				array (
					'key' => 'field_5e4d9f4c5adb5',
					'label' => 'Website',
					'name' => 'website',
					'type' => 'url',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
				),
				array (
					'key' => 'field_5e4d9cd9dff8d',
					'label' => 'Phone',
					'name' => 'phone',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9d0115e53',
					'label' => 'Tollfree',
					'name' => 'tollfree',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9f9c91a47',
					'label' => 'Alternate',
					'name' => 'alternate',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9fbff3169',
					'label' => 'Fax',
					'name' => 'fax',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da1eb56fad',
					'label' => 'Region',
					'name' => 'region',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da0a8d01d3',
					'label' => 'Map Coordinates',
					'name' => 'map_coordinates',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array(
					'key' => 'field_5e84fe9712700',
					'label' => '<span class="dashicons dashicons-welcome-widgets-menus"></span> Details',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4da2ca0d1c7',
					'label' => 'Hours',
					'name' => 'hours',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 8,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da2d73f529',
					'label' => 'Ticket information',
					'name' => 'ticket_information',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
					'maxlength' => '',
					'rows' => 8,
					'new_lines' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da3147f4ae',
					'label' => 'Ticket link',
					'name' => 'ticket_link',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da3b40b08a',
					'label' => 'Admissions & Info',
					'name' => 'admissions_info',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'tabs' => 'all',
          			'toolbar' => 'full',
          			'media_upload' => 1,
				),
				array (
					'key' => 'field_5e4da4886e8a5',
					'label' => 'What It\'s Like',
					'name' => 'what_its_like',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_5e4da4b54906f',
					'label' => 'Don\'t Miss',
					'name' => 'dont_miss',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_542da4c14093c',
					'label' => 'Quote',
					'name' => 'quote_block',
					'type' => 'textarea',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array(
					'key' => 'field_5e84fe2e68fff',
					'label' => '<span class="dashicons dashicons-format-gallery"></span> Images',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4da1b90d60f',
					'label' => 'Images',
					'name' => 'media',
					'type' => 'gallery',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'min' => 0,
					'max' => 0,
					'preview_size' => 'thumbnail',
					'library' => 'all',
					'min_width' => 0,
					'min_height' => 0,
					'min_size' => 0,
					'max_width' => 0,
					'max_height' => 0,
					'max_size' => 0,
					'mime_types' => '.gif, .jpg, .png',
				),
				array(
					'key' => 'field_5e84f917594c9',
					'label' => '<span class="dashicons dashicons-share"></span> Social Media',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4da42cbaffb',
					'label' => '<span class="dashicons dashicons-twitter"></span> Twitter',
					'name' => 'twitter',
					'type' => 'url',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
				),
				array (
					'key' => 'field_5e4da43376704',
					'label' => '<span class="dashicons dashicons-facebook"></span> Facebook',
					'name' => 'facebook',
					'type' => 'url',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
				),
				array (
					'key' => 'field_5e4da43a9109c',
					'label' => '<span class="dashicons dashicons-instagram"></span> Instagram',
					'name' => 'instagram',
					'type' => 'url',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
				),
				array (
					'key' => 'field_5e4da441cf6b4',
					'label' => '<span class="dashicons dashicons-video-alt3"></span> Youtube',
					'name' => 'youtube',
					'type' => 'url',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
				),
				array(
					'key' => 'field_5e84f27312205',
					'label' => '<span class="dashicons dashicons-index-card"></span> Misc',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4d9adae5afa',
					'label' => 'Rank',
					'name' => 'rank',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d98fe0c369',
					'label' => 'Search Keywords',
					'name' => 'search_keywords',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9f5989dbe',
					'label' => 'WCT ID',
					'name' => 'wct_id',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4d9dfec92f5',
					'label' => 'Notification Email',
					'name' => 'notification_email',
					'type' => 'email',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
				),
				array (
					'key' => 'field_5e4da05160eec',
					'label' => 'Notification Interval',
					'name' => 'notification_interval',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5e4da20b181b7',
					'label' => 'Type of Member',
					'name' => 'type_of_member',
					'type' => 'text',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array(
					'key' => 'field_5e84f27312207',
					'label' => '<span class="dashicons dashicons-editor-contract"></span> Amenities',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'HunchSchemaProperty' => '',
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_5e4d981e4c408',
					'label' => 'Amenities',
					'name' => 'amenities',
					'type' => 'wysiwyg',
					'prefix' => '',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'listings',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
		));

		// Events ACF
		acf_add_local_field_group(array (
			'key' => 'group_5e7111e22d8b0',
			'title' => 'Event Fields',
			'fields' => array (
					array(
						'key' => 'field_5e8501be433d2',
						'label' => '<span class="dashicons dashicons-calendar-alt"></span> Event Info',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'placement' => 'left',
						'endpoint' => 0,
					),
					array (
						'key' => 'field_5e71120e56c18',
						'label' => 'Event ID',
						'name' => 'eventid',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 1,
					),
					array (
						'key' => 'field_5e711217e245f',
						'label' => 'Event Type',
						'name' => 'eventtype',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7112217f24f',
						'label' => 'Start Date',
						'name' => 'startdate',
						'type' => 'date_picker',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'display_format' => 'F j, Y',
						'return_format' => 'F j, Y',
						'first_day' => 1,
					),
					array (
						'key' => 'field_5e71122babe6e',
						'label' => 'End Date',
						'name' => 'enddate',
						'type' => 'date_picker',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'display_format' => 'F j, Y',
						'return_format' => 'F j, Y',
						'first_day' => 1,
					),
					array (
						'key' => 'field_5e7113da8d256',
						'label' => 'Event Dates',
						'name' => 'eventdates',
						'type' => 'textarea',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 8,
						'new_lines' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e711241c8cb2',
						'label' => 'Recurrence',
						'name' => 'recurrence',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7113c3534cb',
						'label' => 'Start Time',
						'name' => 'starttime',
						'type' => 'time_picker',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7113ca4da80',
						'label' => 'End Time',
						'name' => 'endtime',
						'type' => 'time_picker',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'display_format' => 'g:i a',
						'return_format' => 'g:i a',
					),
					array (
						'key' => 'field_5e71124996b6e',
						'label' => 'Times',
						'name' => 'times',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'display_format' => 'g:i a',
						'return_format' => 'g:i a',
					),
					array (
						'key' => 'field_5e71125583fad',
						'label' => 'Location',
						'name' => 'location',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e711267b2f0b',
						'label' => '<span class="dashicons dashicons-tickets-alt"></span> Admission',
						'name' => 'admission',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array(
						'key' => 'field_5e850201768f7',
						'label' => '<span class="dashicons dashicons-id-alt"></span> Contact Info',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'placement' => 'left',
						'endpoint' => 0,
					),
					array (
						'key' => 'field_5e71139a916da',
						'label' => 'Contact',
						'name' => 'contact',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7113a7d34ea',
						'label' => 'Email',
						'name' => 'email',
						'type' => 'email',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
					),
					array (
						'key' => 'field_5e971eb9d37ed',
						'label' => 'Website',
						'name' => 'website',
						'type' => 'url',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placeholder' => '',
					),
					array (
						'key' => 'field_5e7112773cf57',
						'label' => 'Address',
						'name' => 'address',
						'type' => 'textarea',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 8,
						'new_lines' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7112834bfd8',
						'label' => 'City',
						'name' => 'city',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e71128bd82fb',
						'label' => 'State',
						'name' => 'state',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7112a8b9130',
						'label' => 'Zip',
						'name' => 'zip',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7112b4069dc',
						'label' => 'Event Region',
						'name' => 'eventregion',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_5e7112bbbf7aa',
						'label' => 'Map Coordinates',
						'name' => 'map_coordinates',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array(
						'key' => 'field_5e8504b277374',
						'label' => '<span class="dashicons dashicons-format-gallery"></span> Images',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'placement' => 'left',
						'endpoint' => 0,
					),
					array (
						'key' => 'field_5e7113ec7404d',
						'label' => 'Images',
						'name' => 'media',
						'type' => 'gallery',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'min' => 0,
						'max' => 0,
						'preview_size' => 'thumbnail',
						'library' => 'all',
						'min_width' => 0,
						'min_height' => 0,
						'min_size' => 0,
						'max_width' => 0,
						'max_height' => 0,
						'max_size' => 0,
						'mime_types' => '.gif, .jpg, .png',
					),
					array(
						'key' => 'field_5e850201768f0',
						'label' => '<span class="dashicons dashicons-index-card"></span> Misc',
						'name' => '',
						'type' => 'tab',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'HunchSchemaProperty' => '',
						'placement' => 'left',
						'endpoint' => 0,
					),
					array(
						'key' => 'field_5e7112c695bbb',
						'label' => 'Featured',
						'name' => 'featured',
						'type' => 'true_false',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'message' => '',
						'default_value' => 0,
						'ui' => 1,
						'ui_on_text' => 'Yes',
						'ui_off_text' => 'No',
					),
					array (
						'key' => 'field_5e7112ce6feb9',
						'label' => 'Listing ID',
						'name' => 'listingid',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 1,
					),
					array (
						'key' => 'field_5e711387e6659',
						'label' => 'Created',
						'name' => 'created',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 1,
					),
					array (
						'key' => 'field_5e711392c3a52',
						'label' => 'Last Updated',
						'name' => 'lastupdated',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 1,
					),
					array (
						'key' => 'field_5e7113b051c50',
						'label' => 'Host Listing ID',
						'name' => 'hostlistingid',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 1,
					),
					array (
						'key' => 'field_5e7113b76c55e',
						'label' => 'Host Name',
						'name' => 'hostname',
						'type' => 'text',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array(
						'key'=> 'field_5e7113d374e6f',
						'label'=> 'Never Expire?',
						'name'=> 'neverexpire',
						'type'=> 'true_false',
						'instructions'=> '',
						'required'=> 0,
						'conditional_logic'=> 0,
						'wrapper'=> array (
							'width'=> '',
							'class'=> '',
							'id'=> '',
						),
						'message'=> '',
						'default_value'=> 0,
						'ui'=> 1,
						'ui_on_text'=> 'Yes',
						'ui_off_text'=> 'No',
					),
					array (
						'key' => 'field_5e7113e5776e6',
						'label' => 'Custom Fields',
						'name' => 'customfields',
						'type' => 'textarea',
						'prefix' => '',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'placeholder' => '',
						'maxlength' => '',
						'rows' => 8,
						'new_lines' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'events',
						),
					),
				),
				'menu_order' => 1,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
			));
		endif;
	} // add_acf_fields

	public function run_bulk_listings_CRON(){

		update_option( 'sv_api_last_run', date("F j, Y, g:i a") );
		update_option( 'sv_api_failure_last_run', false );
		update_option( 'sv_api_method', 'cron' );
		update_option( 'sv_api_kill_cron', getmypid() );
		update_option( 'sv_api_listings_processed', 0 );
		update_option( 'sv_api_listings_updated',  0 );
		update_option( 'sv_api_listings_errors', 0 );
		update_option( 'sv_api_listings_added', 0 );

		$results_num 	= sv_api_connection('getListings', 1);
		$results_count 	= $results_num['REQUESTSTATUS']['RESULTS'];
		update_option( 'sv_api_results_count', $results_count );

		$process_count 	= 0;
		$updated_count 	= 0;
		$error_count 	= 0;
		$added_count 	= 0;
		$kill 			= get_option('sv_api_kill_cron');

		$failed 		= false;
		$api_pagesize 	= 10; // max allowed by Simpleview API is 50
		$num_calls 		= ceil($results_count / $api_pagesize);
		$hasMore 		= true;

		$more_to_do_safeguard = 0;
		$guard_rail = $num_calls * 1.1;

		date_default_timezone_set('America/New_York');

		$log_options = get_option( 'sv_api_logs' );
		[$log_success, $log_folder, $log_file] = createLog($log_options, 'listings', true, $results_count);

		$existing_listing_ids 	= existing_listing_ids();
		$existing_companies 	= existing_companies();

		set_time_limit ( 1800 );

		$page = 0;
		$starting_index = 1;

		while ( $hasMore && ($more_to_do_safeguard < $guard_rail) ){

			$page 		= $page + 1;
			$hasMore 	= $page >= $num_calls ? false : true;

			$response = sv_api_connection('getListings', $api_pagesize, $page);
			if( $response == 'error'):
				$failed = true;
				addPagedFailMessageToLog($log_file, $page);
			endif;
			$listings = $response['LISTINGS']['LISTING'];

			[$processed_this_page,
			$updated_this_page,
			$errors_this_page,
			$added_this_page,
			$this_pages_listings] = process_listings($listings, $existing_listing_ids, $existing_companies);

			$process_count 	+= $processed_this_page;
			$updated_count 	+= $updated_this_page;
			$error_count 	+= $errors_this_page;
			$added_count 	+= $added_this_page;

			addLogData($log_file, $this_pages_listings, $starting_index);

			$total = $results_count;

			if( $failed ):
				update_option( 'sv_api_failure_last_run', true );
			endif;

			$more_to_do_safeguard++;
			$starting_index = $page * $api_pagesize;

		} // while more_to_do

		update_option( 'sv_api_listings_processed', $process_count );
		update_option( 'sv_api_listings_updated', $updated_count );
		update_option( 'sv_api_listings_errors', $error_count );
		update_option( 'sv_api_listings_added', $added_count );

		set_time_limit (30);
		unset($listings);
		unset($response);
		unset($premium_response);

	} //run_bulk_listings

	public function run_bulk_listings(){
			
		$page = intval($_POST['page']);

		/* ==========================================================================
		BULK LISTINGS
		========================================================================== */
			// init_bulk_listings();
			if ( isset($_POST['is_triggered']) && $_POST['is_triggered'] == 'true' ):
			// if ( false ):

				$html = '';

				if( $page == 0 ):
					update_option( 'sv_api_last_run', date("F j, Y, g:i a") );
					update_option( 'sv_api_failure_last_run', false );
					update_option( 'sv_api_method', 'manual' );
					update_option( 'sv_api_listings_processed', 0 );
					update_option( 'sv_api_listings_updated',  0 );
					update_option( 'sv_api_listings_errors', 0 );
					update_option( 'sv_api_listings_added', 0 );
                    // List of listings ids that are processed
                    update_option('sv_api_listings_ids_processed', []);

					$results_num = sv_api_connection('getListings', 1);
					$results_count = $results_num['REQUESTSTATUS']['RESULTS'];
					update_option( 'sv_api_results_count', $results_count );
				endif; // page == 0


				$process_count 	= get_option( 'sv_api_listings_processed' );
				$updated_count 	= get_option( 'sv_api_listings_updated' );
				$error_count 	= get_option( 'sv_api_listings_errors' );
				$added_count 	= get_option( 'sv_api_listings_added' );

				$failed 		= false;
				$results_count 	= get_option( 'sv_api_results_count' );
				$api_pagesize 	= 10; // max allowed by Simpleview API is 50
				$num_calls 		= ceil($results_count / $api_pagesize);
				$page 			= $page + 1;
				$hasMore 		= $page >= $num_calls ? false : true;

				date_default_timezone_set('America/New_York');

				if ($page == 1) {
					$log_options = get_option( 'sv_api_logs' );
					[$log_success, $log_folder, $log_file] = createLog($log_options, 'listings', true, $results_count);
				}

				$existing_listing_ids 	= existing_listing_ids();
				$existing_companies 	= existing_companies();

				$response = sv_api_connection('getListings', $api_pagesize, $page);

				if( $response == 'error'):
					$failed = true;
				endif;
				$listings = $response['LISTINGS']['LISTING'];

				[$processed_this_page,
				$updated_this_page,
				$errors_this_page,
				$added_this_page,
				$this_pages_listings] = process_listings($listings, $existing_listing_ids, $existing_companies);

				$starting_index = ($page - 1)*$api_pagesize;
				$log_options 	= get_option( 'sv_api_logs' );
				$log_file 		= getLogFile($log_options, 'listings');
				addLogData($log_file, $this_pages_listings, $starting_index);

				$process_count 	= get_option( 'sv_api_listings_processed' );
				$updated_count 	= get_option( 'sv_api_listings_updated' );
				$error_count 	= get_option( 'sv_api_listings_errors' );
				$added_count 	= get_option( 'sv_api_listings_added' );
                // List of listings ids that have already been imported
                $processed_listings_ids = get_option( 'sv_api_listings_ids_processed', [] );
                $processed_listings_ids = is_array($processed_listings_ids) ? array_merge($processed_listings_ids, array_keys($this_pages_listings)) : array_keys($this_pages_listings);

				update_option( 'sv_api_listings_processed', $process_count + $processed_this_page );
				update_option( 'sv_api_listings_updated', $updated_count + $updated_this_page );
				update_option( 'sv_api_listings_errors', $error_count + $errors_this_page );
				update_option( 'sv_api_listings_added', $added_count + $added_this_page );
                update_option( 'sv_api_listings_ids_processed', $processed_listings_ids );

				// TODO: make a function to add this front end data

				$total = $results_count;
				$percent = round( ( $page / $num_calls ) * 100,2 );

				if( $failed ):
					$html .= '<br>Page ' . $page . ' of ' . $num_calls . ' FAILED .... ' . $percent . '%<br>';
					update_option( 'sv_api_failure_last_run', true );
				else:
					$html .= '<div style="margin-top: 30px; margin-bottom: 10px;">'.
											'Page '. $page . ' of ' . $num_calls . ' completed: ' .
											$processed_this_page . ' Processed, '.
											$added_this_page . ' Added, '.
											$updated_this_page . ' Updated, '.
											$errors_this_page . ' Errors '. '.... ' . $percent . '%'.
										'</div>';
				endif;


				if ( count($this_pages_listings) ){
					foreach ($this_pages_listings as $listing_status) {
						$html .= '<div style="margin-left:25px;">'.$listing_status[0] . ' -- ' . $listing_status[1].'</div>';
					}
				}

                // Update listings status
                if ( !$hasMore ) {
                    $this->update_absent_listings_status();
                }

				$data = array(
				    'page'    => $page,
				    'num_calls' => $num_calls,
				    'api_pagesize' => $api_pagesize,
				    'hasMore' => $hasMore,
						'logData' => $html,
						'results_count'   => $results_count,
						'added_count'   => $added_this_page,
						'failed'  => $failed,
						'percent' => $percent
				);
				wp_send_json($data);
			endif; // if is_triggered
	} //run_bulk_listings

    /**
     * Make listings that are not included in the latest API response as drafts
     */
    public function update_absent_listings_status() {
        global $wpdb;

        // Grabbing all the current listings
        $existing_listing_ids = get_all_current_listings();
        // Make it as array of wp IDs
        $existing_listing_ids = array_map(
            function($listing) {
                return $listing->ID;
            },
            $existing_listing_ids
        );
        // Grab all the listings processed from the last import
        $processed_listings_ids = get_option('sv_api_listings_ids_processed', []);
        if (!is_array($processed_listings_ids) || empty($processed_listings_ids)) {
            return;
        }

        $active = [];
        $inactive = [];
        foreach ($existing_listing_ids as $listing_id) {
            if (in_array($listing_id, $processed_listings_ids)) {
                $active[] = $listing_id;
            } else {
                $inactive[] = $listing_id;
            }
        }
        $active = implode(',', $active);
        $inactive = implode(',', $inactive);

        if ($active) {
            $wpdb->query("
                UPDATE $wpdb->posts
                SET post_status = 'publish'
                WHERE ID IN ($active)
            ");
        }
        if ($inactive) {
            $wpdb->query("
                UPDATE $wpdb->posts
                SET post_status = 'draft'
                WHERE ID IN ($inactive)
            ");
        }
    }

	public function run_bulk_events_CRON(){
		// TODO: Implement cron update
		// process_events('cron');
	} //run_bulk_events_CRON

	public function run_bulk_events(){
		$page = intval($_POST['page']);
		if ( isset($_POST['is_triggered']) && $_POST['is_triggered'] == 'true' ):
			$data = process_events($page, 'manual');
			wp_send_json($data);
		endif; 
	} //run_bulk_events

	public function run_bulk_coupons() {
		$page = 0;

		$coupons = sv_api_connection('getCoupons', 50, $page)['COUPONS']['COUPON'];

		while ( is_array($coupons) && !($page > 10) ) {

			$cat_array = array();

			foreach ($coupons as $coupon) {

				$our_listing = new WP_Query(
										array(
										'post_type'      => 'listings',
										'post_status' 	 => array('publish'),
										'posts_per_page' => 1,
										'meta_query' => array(
															array(
																'key' => 'listing_id',
																'value' => $coupon['LISTINGID'],
																'compare' => '='
															)
														)
										)
									);

				if (isset($our_listing->posts[0])) {
					$our_listing_id = $our_listing->posts[0]->ID;
				}
				else {
					$our_listing_id = false;
				}

				$coupon_array = array();

				$category = $coupon['CATNAME'];
				$coupon_array['coupon_id'] = $coupon['COUPONID'];
				$coupon_array['our_listing_id'] = $our_listing_id;
				$coupon_array['title'] = '';
				$coupon_array['company'] = '';
				$coupon_array['address'] = '';
				$coupon_array['url'] = '';
				$coupon_array['copy'] = '';
				$coupon_array['startdate'] = '';
				$coupon_array['enddate'] = '';
				$coupon_array['image'] = false;
				$coupon_array['image_id'] = false;


				if ( (!is_array($coupon['ADDR1'])) ) {
					$coupon_array['title'] = $coupon['OFFERTITLE'];
				}
				if ( (!is_array($coupon['SORTCOMPANY'])) ) {
					$coupon_array['company'] = $coupon['SORTCOMPANY'];
				}
				$coupon_array['address'] = "";
				if ( (!is_array($coupon['ADDR1'])) ) {
					$coupon_array['address'] = $coupon['ADDR1'];
				}
				if ( (!is_array($coupon['CITY'])) ) {
					$coupon_array['address'] .= $coupon['CITY'].", Maryland ";
				}
				if ( (!is_array($coupon['ZIP'])) ) {
					$coupon_array['address'] .= $coupon['ZIP'];
				}
				if ( (!is_array($coupon['OFFERLINK'])) ) {
					$coupon_array['url'] = $coupon['OFFERLINK'];
				}
				else if ( (!is_array($coupon['WEBURL'])) ) { // Fallback URL
					$coupon_array['url'] = $coupon['WEBURL'];
				}
				if ( (!is_array($coupon['OFFERTEXT'])) ) {
					$coupon_array['copy'] = $coupon['OFFERTEXT'];
				}
				if ( (!is_array($coupon['REDEEMSTART'])) ) {
					$split_start_date = explode("-", $coupon['REDEEMSTART']);
					$reform_start_date = $split_start_date[2].$split_start_date[0].$split_start_date[1];
					$coupon_array['startdate'] = $reform_start_date;
				}
				if ( (!is_array($coupon['REDEEMEND'])) ) {
					$split_end_date = explode("-", $coupon['REDEEMEND']);
					$reform_end_date = $split_end_date[2].$split_end_date[0].$split_end_date[1];
					$coupon_array['enddate'] = $reform_end_date;
				}
				if ( (!is_array($coupon['MEDIAID'])) ) {
					$coupon_array['image'] = $coupon['IMGPATH'].$coupon['MEDIAFILE'];
					$coupon_array['image_id'] = $coupon['MEDIAID'];
				}
				else {
					$coupon_array['image'] = false;
					$coupon_array['image_id'] = false;
				}
				if (isset($category)) {
					if ( isset($cat_array[$category]) ) {
						$cat_array[$category][] = $coupon_array;
					}
					else {
						$cat_array[$category] = array();
						$cat_array[$category][] = $coupon_array;
					}
				}
				else {
					if ( isset($cat_array["Other"]) ) {
						$cat_array["Other"][] = $coupon_array;
					}
					else {
						$cat_array["Other"] = array();
						$cat_array["Other"][] = $coupon_array;
					}
				}
			}

			foreach ($cat_array as $catname => $category) {

				$tag = get_term_by('name', $catname, 'post_tag');

				if ($tag) {
					$tag_id = $tag->term_id;
				}
				else { // create the tag
					$tag_id = wp_insert_term(
						$catname, // the term
						'post_tag', // the taxonomy
						array(
							'slug' => sanitize_title($catname),
						)
					);
					$tag = get_term_by('name', $catname, 'post_tag');
				}

				foreach ($category as $coupon) {
					$coupon_post = new WP_Query(
						array(
							'post_type'      => 'coupons',
							'post_status' 	 => array('publish', 'trash'),
							'posts_per_page' => 1,
							'meta_query' => array(
												array(
													'key' => 'offer_id',
													'value' => $coupon['coupon_id'],
													'compare' => '='
												)
											)
						)
					);

					if ( count($coupon_post->posts) ) { //post already exists
						$pid = $coupon_post->posts[0]->ID;

						// if the post is old, add it to the trash
						if ( isset( $coupon['enddate'] ) ) {
							if ( $coupon['enddate'] ) {
								$formatted_date = str_replace('-', '/', $coupon['enddate']);
								
								if ( strtotime( $formatted_date ) < time() ) {
									wp_trash_post($pid);
								}
							}
						}

						if ($coupon['image']) {

							$check_image_args = array(
								'post_type'      => 'attachment',
								//'post_mime_type' => 'image',
								'post_status'    => 'any',
								'posts_per_page' => -1,
								'meta_key'      => 'simpleview_id',
								'meta_value'    =>  $coupon['image_id']
							);

							$check_image = new WP_Query( $check_image_args );

							if ( !($check_image->posts) ) { // image does not exist
								$attachment_id = saveImageToWP($coupon['image'], $pid);
								if (!is_wp_error($attachment_id)) {
									update_field('simpleview_id', $coupon['image_id'], $attachment_id);
									set_post_thumbnail($pid, $attachment_id);
								}
							}
						}


						update_field('offer_id', $coupon['coupon_id'], $pid);

						$overwrite_category = get_field('overwrite_category', $pid);
						$overwrite_title = get_field('overwrite_title', $pid);
						$overwrite_company = get_field('overwrite_company', $pid);
						$overwrite_address = get_field('overwrite_address', $pid);
						$overwrite_link = get_field('overwrite_link', $pid);
						$overwrite_offer_copy = get_field('overwrite_offer_copy', $pid);

						if (!$overwrite_category) {
							update_field('offer_category', $tag_id, $pid);
						}
						if (!$overwrite_title) {
							update_field('title', $coupon['title'], $pid);
						}
						if (!$overwrite_company) {
							update_field('company', $coupon['company'], $pid);
						}
						if (!$overwrite_address) {
							update_field('address', $coupon['address'], $pid);
						}
						if (!$overwrite_offer_copy) {
							update_field('offer_copy', $coupon['copy'], $pid);
						}
						if ( !$overwrite_link  ) {
							update_field('link_to_offer', $coupon['url'], $pid);
						}

						update_field('internal_listing_id', $coupon['our_listing_id'], $pid);


						if ( isset( $coupon['startdate'] ) ) {
							update_field('offer_start_date', $coupon['startdate'], $pid);
						}
						if ( isset( $coupon['enddate'] ) ) {
							update_field('offer_end_date', $coupon['enddate'], $pid);
						}

					}
					else { // new post

						$post = array(
							'post_author'   => 1,
							'post_status'   => 'publish',
							'post_type'     => 'coupons'
						);

						if (isset($coupon['title'])) {
							$post['post_title'] = $coupon['title'];
						}
						else {
							$post['post_title'] = "Coupon ".$coupon['coupon_id'];
						}

						$pid = wp_insert_post($post, true);

						if ($coupon['image']) {
							$attachment_id = saveImageToWP($coupon['image'], $pid);
							if (!is_wp_error($attachment_id)) {
								update_field('simpleview_id', $coupon['image_id'], $attachment_id);
							}
						}
						set_post_thumbnail($pid, $attachment_id);

						update_field('offer_id', 	$coupon['coupon_id'], $pid);
						update_field('offer_category', $tag_id, $pid);
						update_field('title', $coupon['title'], $pid);
						update_field('company', $coupon['company'], $pid);
						update_field('address', $coupon['address'], $pid);
						update_field('link_to_offer', $coupon['url'], $pid);
						update_field('offer_start_date', $coupon['startdate'], $pid);
						update_field('offer_end_date', $coupon['enddate'], $pid);
						update_field('offer_copy', $coupon['copy'], $pid);
						update_field('internal_listing_id', $coupon['our_listing_id'], $pid);
					}
				}
			}

			$page++;

			$couponResponse = sv_api_connection('getCoupons', 50, $page);
			if (isset( $couponResponse['COUPONS']['COUPON'] )) {
				$coupons = $couponResponse['COUPONS']['COUPON'];
			}
			else {
				$coupons = false;
			}

		}
	}

/* ==========================================================================
UPDATES
========================================================================== */

	public function run_single_listing_import() {

		$data = (object)[
			"postFound" => false
		];

		if ($_POST['idType'] == "sv") { //user is using simpleview ID

			if (isset($_POST['pid'])) {
				$query_options = array(
				  'post_type'      => 'listings',
				  'posts_per_page' => 1,
				  'fields'         => 'ids',
				  'meta_query' => array(
				  					array(
				  						'key'     => 'listing_id',
				  						'compare' => '==',
				  						'value'		=> $_POST['pid']
				  					),
				  				),
				);

				$query_response = new WP_Query($query_options);

				if ( count($query_response->posts) ) { // we found the wordpress post with that SVID
					$the_pid = $query_response->posts[0];
					$data->postFound = true;
					$data->pid = $the_pid;
					$data->svid = intval(get_field("listing_id", $the_pid));
					$data->link = get_permalink($the_pid);

					$SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $data->svid);
					$update_listing_result = update_listing($SV_API_RESPONSE, $the_pid);

					$data->status = $update_listing_result[0];
					$data->returnMessage = $update_listing_result[1];
				}
				else { // there is no wordpress post, we will create a new one
					$data->createNew = true;
					$data->svid = $_POST['pid'];
				}
			}
		}
		else { // user is using WP Post ID
			if (isset($_POST['pid'])) {

				$query_options = array(
				  'post_type'      => 'listings',
				  'posts_per_page' => 1,
				  'fields'         => 'ids',
				  'p'			 				 => $_POST['pid']
				);

				$query_response = new WP_Query($query_options);

				if ( count($query_response->posts) ) { //post has been found

					$the_pid = $query_response->posts[0];
					$data->postFound = true;
					$data->pid = $the_pid;
					$data->link = get_permalink($the_pid);
					$data->svid = intval(get_field("listing_id", $the_pid));

					$SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $data->svid);

					$update_listing_result = update_listing($SV_API_RESPONSE, $the_pid);

					$data->status = $update_listing_result[0];
					$data->returnMessage = $update_listing_result[1];

				}

			}
		}

		$encoded_json = json_encode($data);

		echo $encoded_json;

		die();
	}

	public function create_new_post_from_svid() {
		$data = (object)[
			"postFound" => false
		];

		if ( isset($_POST['svid']) ) {
			$data->svid = $_POST['svid'];

			$SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $data->svid);

			$create_new_listing_result = create_new_listing($SV_API_RESPONSE);

			$data->status = $create_new_listing_result[0];
			$data->returnMessage = $create_new_listing_result[1];
			$data->link = $create_new_listing_result[2];
		}

		$encoded_json = json_encode($data);

		echo $encoded_json;

		die();
	}

	public function kill_cron() {
		// if the cron is in the middle of running, setting this will terminate the job
		
		$kill_return = posix_kill ( intval( get_option('sv_api_kill_cron') ) , SIGKILL );

		$data = (object)[
			"cronKilled" => true
		];

		$encoded_json = json_encode($data);

		echo $encoded_json;

		die();
	}
}

//testing