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

    const COUPONS_PAGE_LIMIT = 5;
    const COUPONS_IMPORT_CRON_HOOK = 'run_coupons_import_by_page';

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
        register_taxonomy(
            'listings-category',
            null,
            array(
                'labels' => array(
                    'name'                       => _x( 'Listing Categories', 'taxonomy general name', 'textdomain' ),
                    'singular_name'              => _x( 'Listing Category', 'taxonomy singular name', 'textdomain' ),
                    'search_items'               => __( 'Search Listing Categories', 'textdomain' ),
                    'popular_items'              => __( 'Popular Listing Categories', 'textdomain' ),
                    'all_items'                  => __( 'All Listing Categories', 'textdomain' ),
                    'parent_item'                => null,
                    'parent_item_colon'          => null,
                    'edit_item'                  => __( 'Edit Listing Categories', 'textdomain' ),
                    'update_item'                => __( 'Update Listing Categories', 'textdomain' ),
                    'add_new_item'               => __( 'Add New Listing Categories', 'textdomain' ),
                    'new_item_name'              => __( 'New Listing Categories Name', 'textdomain' ),
                    'separate_items_with_commas' => __( 'Separate Listing Categories with commas', 'textdomain' ),
                    'add_or_remove_items'        => __( 'Add or remove Listing Categories', 'textdomain' ),
                    'choose_from_most_used'      => __( 'Choose from the most used Listing Categories', 'textdomain' ),
                    'not_found'                  => __( 'No Listing Categories found.', 'textdomain' ),
                    'menu_name'                  => __( 'Listing Categories', 'textdomain' ),
                ),
                'public' => true,
                'hierarchical' => true,
                'rewrite' => array( 'slug' => 'listings-category', 'with_front'=> true )
            ),
            true
        );

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
                'taxonomies' => array('post_tag', 'listings-category'),
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
                    array(
                        'key' => 'field_631efd9c183af',
                        'label' => 'Menu',
                        'name' => 'rwmenu',
                        'type' => 'url',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
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

    public function run_bulk_listings() {
        if (!isset($_POST['is_triggered']) || $_POST['is_triggered'] != 'true') {
            return;
        }

        // BULK LISTINGS
        $page = intval($_POST['page']);
        $html = '';

        if ($page == 0) {
            update_option('sv_api_last_run', date("F j, Y, g:i a"));
            update_option('sv_api_failure_last_run', false);
            update_option('sv_api_method', 'manual');
            update_option('sv_api_listings_processed', 0);
            update_option('sv_api_listings_updated', 0);
            update_option('sv_api_listings_errors', 0);
            update_option('sv_api_listings_added', 0);
            // List of listings ids that are processed
            update_option('sv_api_listings_ids_processed', []);

            $results_num   = sv_api_connection('getListings', 1);
            $results_count = $results_num['REQUESTSTATUS']['RESULTS'] ?? '0';
            update_option('sv_api_results_count', $results_count);
        }


        $process_count = get_option('sv_api_listings_processed');
        $updated_count = get_option('sv_api_listings_updated');
        $error_count   = get_option('sv_api_listings_errors');
        $added_count   = get_option('sv_api_listings_added');

        $failed        = false;
        $results_count = get_option('sv_api_results_count');
        $api_pagesize  = 10; // max allowed by Simpleview API is 50
        $num_calls     = ceil($results_count / $api_pagesize);
        $page          = $page + 1;
        $hasMore       = ! ($page >= $num_calls);

        date_default_timezone_set('America/New_York');

        if ($page == 1) {
            $log_options                           = get_option('sv_api_logs');
            [$log_success, $log_folder, $log_file] = createLog($log_options, 'listings', true, $results_count);
        }

        $existing_listing_ids = existing_listing_ids();
        $existing_companies   = existing_companies();

        $response = sv_api_connection('getListings', $api_pagesize, $page);

        if ($response == 'error') {
            $failed = true;
        }

        $listings = $response['LISTINGS']['LISTING'] ?? [];

        [
            $processed_this_page,
            $updated_this_page,
            $errors_this_page,
            $added_this_page,
            $this_pages_listings
        ] = process_listings($listings, $existing_listing_ids, $existing_companies);

        $starting_index = ($page - 1) * $api_pagesize;
        $log_options    = get_option('sv_api_logs');
        $log_file       = getLogFile($log_options, 'listings');
        addLogData($log_file, $this_pages_listings, $starting_index);

        $process_count = get_option('sv_api_listings_processed');
        $updated_count = get_option('sv_api_listings_updated');
        $error_count   = get_option('sv_api_listings_errors');
        $added_count   = get_option('sv_api_listings_added');

        // List of listings ids that have already been imported
        $processed_listings_ids = get_option('sv_api_listings_ids_processed', []);
        $processed_listings_ids = array_merge(
            is_array($processed_listings_ids) ? $processed_listings_ids : [],
            array_keys($this_pages_listings)
        );

        update_option('sv_api_listings_processed', $process_count + $processed_this_page);
        update_option('sv_api_listings_updated', $updated_count + $updated_this_page);
        update_option('sv_api_listings_errors', $error_count + $errors_this_page);
        update_option('sv_api_listings_added', $added_count + $added_this_page);
        update_option('sv_api_listings_ids_processed', $processed_listings_ids);

        // TODO: make a function to add this front end data

        $total   = $results_count;
        $percent = $num_calls ? round(($page / $num_calls) * 100, 2) : 100;

        if ($failed) {
            $html .= '<br>Page ' . $page . ' of ' . $num_calls . ' FAILED .... ' . $percent . '%<br>';
            update_option('sv_api_failure_last_run', true);
        } else {
            $html .= '<div style="margin-top: 30px; margin-bottom: 10px;">' . 'Page ' . $page . ' of ' . $num_calls . ' completed: ' . $processed_this_page . ' Processed, ' . $added_this_page . ' Added, ' . $updated_this_page . ' Updated, ' . $errors_this_page . ' Errors ' . '.... ' . $percent . '%' . '</div>';
        }

        if (count($this_pages_listings)) {
            foreach ($this_pages_listings as $listing_status) {
                $html .= '<div style="margin-left:25px;">' . $listing_status[0] . ' -- ' . $listing_status[1] . '</div>';
            }
        }

        // Update listings status
        if (! $hasMore) {
            $this->update_absent_listings_status();
        }

        $data = array(
            'page'          => $page,
            'num_calls'     => $num_calls,
            'api_pagesize'  => $api_pagesize,
            'hasMore'       => $hasMore,
            'logData'       => $html,
            'results_count' => $results_count,
            'added_count'   => $added_this_page,
            'failed'        => $failed,
            'percent'       => $percent,
        );
        wp_send_json($data);
    }

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

    /**
     * Running the coupons importing process
     */
    public function run_bulk_coupons():void {

        $new_task = $this->add_new_single_cron_task_for_coupons(1);

        if ($new_task) {
            wp_send_json_success([
                'message' => 'The process of coupons import has been started',
            ]);
        }

        wp_send_json_error([
            'message' => 'The process of coupons import is already going!',
        ]);
    }

    /**
     * Adds a single CRON job task for importing the passed page number of coupons SV API response list
     * @param int $page
     * @return bool
     */
    private function add_new_single_cron_task_for_coupons(int $page = 0):bool {
        $pages_limit = self::COUPONS_PAGE_LIMIT;

        if( ! wp_next_scheduled( self::COUPONS_IMPORT_CRON_HOOK ) && $page <= $pages_limit ) {
            $event = wp_schedule_single_event(time() + 1, self::COUPONS_IMPORT_CRON_HOOK, [
                'page' => $page,
            ]);
            if (! $event) {
                update_option('current_coupons_import_page', -1);
            }

            return $event;
        }

        update_option('current_coupons_import_page', -1);

        return false;
    }

    /**
     * Returns a page number that is being processed in a current coupons import task
     * @return int
     */
    public static function get_current_page_for_coupons_import(): int {
        return (int) get_option('current_coupons_import_page', -1);
    }

    /**
     * Returns a page limit for the coupons import
     * @return int
     */
    public static function get_page_limits_for_coupons_import(): int {
        return self::COUPONS_PAGE_LIMIT;
    }

    /**
     * Importing coupons from the passed page number to WordPress. Typically, is being used as a CRON job
     * @param int $page
     */
    public function run_coupons_import_by_page(int $page):void {
        $page_size = 50;

        update_option('current_coupons_import_page', $page);

        try {
            // Getting coupons for this page from the SV API
            $coupons = sv_api_connection('getCoupons', $page_size, $page);
            if (! isset($coupons['COUPONS']['COUPON']) || !is_array($coupons['COUPONS']['COUPON']) || empty($coupons['COUPONS']['COUPON'])) {
                update_option('current_coupons_import_page', -1);
                return;
            }
            $coupons = $coupons['COUPONS']['COUPON'];

            $processed_coupons = [];
            foreach ($coupons as $coupon) {

                // All the needed data for this coupon
                $coupon_array = $this->collect_single_coupon_data($coupon);
                // Add/Update the coupon
                $processed_coupons[] = $this->update_or_add_single_coupon($coupon_array);
            }

            // Adding a new cron task for the next page
            $this->add_new_single_cron_task_for_coupons($page + 1);
        } catch (Throwable $e) {
            update_option('current_coupons_import_page', -1);
        }

    }

    /**
     * Collect the needed coupon data from the passed data
     * @param array $data
     * @return array
     */
    public function collect_single_coupon_data(array $data): array {
        $coupon_data = [];

        // Related Listing ID
        $coupon_data['our_listing_id'] = $this->get_coupon_listing(absint($data['LISTINGID']));

        // Categories info
        $coupon_data['categories'] = [];
        if (isset($data['CATID']) && $data['CATID']) {
            $coupon_data['categories']['category'] = [
                'id' => absint($data['CATID']),
                'name' => (string) $data['CATNAME'],
            ];
        }
        if (isset($data['SUBCATID']) && $data['SUBCATID']) {
            $coupon_data['categories']['subcategory'] = [
                'id' => absint($data['SUBCATID']),
                'name' => (string) $data['SUBCATNAME'],
            ];
        }

        // ID
        $coupon_data['coupon_id'] = $data['COUPONID'];
        // Title
        $coupon_data['title'] = ! is_array($data['ADDR1']) ? (string) $data['OFFERTITLE'] : '';
        // Company Name
        $coupon_data['company'] = ! is_array($data['SORTCOMPANY']) ? (string) $data['SORTCOMPANY'] : '';
        // Address. Can consist of several address parts (street, city, zip etc)
        $coupon_data['address'] = [];
        $coupon_data['address'][] = ! is_array($data['ADDR1']) ? (string) $data['ADDR1'] : '';
        $coupon_data['address'][] = ! is_array($data['CITY']) ? (string) $data['CITY'] : '';
        $coupon_data['address'][] = 'Maryland';
        $coupon_data['address'][] = ! is_array($data['ZIP']) ? (string) $data['ZIP'] : '';
        // Removing empty items from the address array
        $coupon_data['address'] = array_filter($coupon_data['address'], 'strlen');
        // Merging into one string
        $coupon_data['address'] = implode(', ', $coupon_data['address']);
        // URL
        $coupon_data['url'] = ! is_array($data['OFFERLINK']) ? (string) $data['OFFERLINK'] : (! is_array($data['WEBURL']) ? (string) $data['WEBURL'] : '');
        // Copy
        $coupon_data['copy'] = ! is_array($data['OFFERTEXT']) ? (string) $data['OFFERTEXT'] : '';

        // Start Date
        if (! is_array($data['REDEEMSTART']) && $data['REDEEMSTART']) {
            $split_start_date = explode("-", $data['REDEEMSTART']);
            $reform_start_date = $split_start_date[2] . $split_start_date[0] . $split_start_date[1];
            $coupon_data['startdate'] = $reform_start_date;
        } else {
            $coupon_data['startdate'] = '';
        }

        // End Date
        if (! is_array($data['REDEEMEND']) && $data['REDEEMEND']) {
            $split_end_date = explode("-", $data['REDEEMEND']);
            $reform_end_date = $split_end_date[2] . $split_end_date[0] . $split_end_date[1];
            $coupon_data['enddate'] = $reform_end_date;
        } else {
            $coupon_data['enddate'] = '';
        }

        // Media Info
        if (! is_array($data['MEDIAID']) && isset($data['IMGPATH']) && isset($data['MEDIAFILE'])) {
            $coupon_data['image'] = $data['IMGPATH'] . $data['MEDIAFILE'];
            $coupon_data['image_id'] = $data['MEDIAID'];
        } else {
            $coupon_data['image'] = null;
            $coupon_data['image_id'] = null;
        }

        return $coupon_data;
    }

    /**
     * Returns a listing wordpress ID by its SV ID
     * @param int $coupon_id
     * @return int|null
     */
    public function get_coupon_listing(int $coupon_id):?int {
        $our_listing = get_posts([
            'post_type' => 'listings',
            'post_status' => ['publish'],
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'listing_id',
                    'value' => $coupon_id,
                    'compare' => '=',
                ],
            ]
        ]);

        if (! empty($our_listing)) {
            return $our_listing[0]->ID;
        }

        return null;
    }

    /**
     * Update an existing coupon or adding a new one depending on the passed data
     * @param array $coupon_data
     * @return int Coupon ID
     */
    public function update_or_add_single_coupon(array $coupon_data): int {
        $categories = $this->update_or_add_coupon_categories((array) $coupon_data['categories']);

        $existing_coupon = get_posts([
            'post_type' => 'coupons',
            'post_status' => ['publish', 'trash'],
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'offer_id',
                    'value' => $coupon_data['coupon_id'],
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
            ],
        ]);

        $existing_coupon_id = !empty($existing_coupon) ? $existing_coupon[0]->ID : null;
        if (! is_null($existing_coupon_id)) {
            return $this->update_single_coupon($coupon_data, $existing_coupon_id, $categories);
        } else {
            return $this->add_single_coupon($coupon_data, $categories);
        }
    }

    /**
     * Returns an existing category for the passed category ID. Creates a new one if it does not exist yet
     * @param array $categories
     * @return array
     */
    public function update_or_add_coupon_categories(array $categories = []): array {
        $wp_categories = [];

        if ( isset($categories['category']['id']) ) {
            $category = manageCategory([
                'id' => absint($categories['category']['id']),
                'name' => $categories['category']['name'] ?? '',
            ]);
        }
        if (isset($category)) {
            $wp_categories[] = $category;
        }

        if ( isset($categories['subcategory']['id']) ) {
            $subcategory = manageCategory([
                'id' => absint($categories['subcategory']['id']),
                'name' => $categories['subcategory']['name'] ?? '',
                'parent' => $category ?? 0,
            ]);
        }
        if (isset($subcategory)) {
            $wp_categories[] = $subcategory;
        }



        return $wp_categories;
    }

    /**
     * Adding a single coupon with the passed data
     * @param array $coupon_data
     * @param array $categories
     * @return int
     */
    public function add_single_coupon(array $coupon_data, array $categories = []): int {
        $post = array(
            'post_author' => 1,
            'post_status' => 'publish',
            'post_type' => 'coupons'
        );

        if (isset($coupon_data['title'])) {
            $post['post_title'] = $coupon_data['title'];
        } else {
            $post['post_title'] = "Coupon " . $coupon_data['coupon_id'];
        }

        $coupon_id = wp_insert_post($post, true);

        // Assign to categories
        if (! empty($categories)) {
            wp_set_object_terms($coupon_id, $categories, 'category');
        }

        // Append coupon's image
        if (isset($coupon_data['image_id']) && ! is_null($coupon_data['image_id'])) {
            $attachment_id = saveImageToWP($coupon_data['image'], $coupon_id);
            if (! is_wp_error($attachment_id)) {
                update_field('simpleview_id', $coupon_data['image_id'], $attachment_id);
                set_post_thumbnail($coupon_id, $attachment_id);
            }
        }

        update_field('offer_id', $coupon_data['coupon_id'], $coupon_id);
        update_field('title', $coupon_data['title'], $coupon_id);
        update_field('company', $coupon_data['company'], $coupon_id);
        update_field('address', $coupon_data['address'], $coupon_id);
        update_field('link_to_offer', $coupon_data['url'], $coupon_id);
        update_field('offer_start_date', $coupon_data['startdate'], $coupon_id);
        update_field('offer_end_date', $coupon_data['enddate'], $coupon_id);
        update_field('offer_copy', $coupon_data['copy'], $coupon_id);
        update_field('internal_listing_id', $coupon_data['our_listing_id'], $coupon_id);

        return $coupon_id;
    }

    /**
     * Update a passed coupon with the passed data
     * @param array $coupon_data
     * @param int $coupon_id
     * @param array $categories
     * @return int
     */
    public function update_single_coupon(array $coupon_data, int $coupon_id, array $categories = []): int {

        // if the post is old, add it to the trash
        if (isset($coupon_data['enddate']) && $coupon_data['enddate']) {
            $formatted_date = str_replace('-', '/', $coupon_data['enddate']);

            if (strtotime($formatted_date) < time()) {
                wp_trash_post($coupon_id);
            }
        }

        if (isset( $coupon_data['image_id'] ) && ! is_null($coupon_data['image_id'])) {
            $check_image = get_posts([
                'post_type' => 'attachment',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_key' => 'simpleview_id',
                'meta_value' => $coupon_data['image_id'],
            ]);

            if (empty($check_image)) {
                $attachment_id = saveImageToWP($coupon_data['image'], $coupon_id);
                if (! is_wp_error($attachment_id)) {
                    update_field('simpleview_id', $coupon_data['image_id'], $attachment_id);
                    set_post_thumbnail($coupon_id, $attachment_id);
                }
            }
        }

        // Assign to categories
        if (! empty($categories)) {
            wp_set_object_terms($coupon_id, $categories, 'category');
        }

        if (! get_field('overwrite_title', $coupon_id)) {
            update_field('title', $coupon_data['title'], $coupon_id);
        }
        if (! get_field('overwrite_company', $coupon_id)) {
            update_field('company', $coupon_data['company'], $coupon_id);
        }
        if (! get_field('overwrite_address', $coupon_id)) {
            update_field('address', $coupon_data['address'], $coupon_id);
        }
        if (! get_field('overwrite_link', $coupon_id)) {
            update_field('link_to_offer', $coupon_data['url'], $coupon_id);
        }
        if (! get_field('overwrite_offer_copy', $coupon_id)) {
            update_field('offer_copy', $coupon_data['copy'], $coupon_id);
        }


        update_field('internal_listing_id', $coupon_data['our_listing_id'], $coupon_id);


        if (isset($coupon_data['startdate'])) {
            update_field('offer_start_date', $coupon_data['startdate'], $coupon_id);
        }
        if (isset($coupon_data['enddate'])) {
            update_field('offer_end_date', $coupon_data['enddate'], $coupon_id);
        }

        return $coupon_id;
    }

/* ==========================================================================
UPDATES
========================================================================== */

    public function run_single_listing_import() {
        $data = (object)[
            "postFound" => false
        ];

        if (isset($_POST['pid'])) {
            if ($_POST['idType'] == "sv") {
                $query_options = array(
                    'post_type' => 'listings',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => 'listing_id',
                            'compare' => '==',
                            'value' => $_POST['pid']
                        ),
                    ),
                );
            } else { // user is using WP Post ID
                $query_options = array(
                    'post_type' => 'listings',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'p' => $_POST['pid']
                );
            }

            $query_response = new WP_Query($query_options);

            if (count($query_response->posts)) {
                $the_pid = $query_response->posts[0];
                $data->postFound = true;
                $data->pid = $the_pid;
                $data->svid = intval(get_field("listing_id", $the_pid));
                $data->link = get_permalink($the_pid);

                $SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $data->svid);

                $isFeatured = (isset($SV_API_RESPONSE['LISTING']['DTN']['RANK']) && (int)$SV_API_RESPONSE['LISTING']['DTN']['RANK'] > 0);
                $update_listing_result = update_listing($SV_API_RESPONSE, $the_pid, $isFeatured);

                $data->status = $update_listing_result[0];
                $data->returnMessage = $update_listing_result[1];
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

/* vim: set ts=4 sw=4 sts=4 et : */
