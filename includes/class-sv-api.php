<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://bellweather.agency/
 * @since      1.0.0
 *
 * @package    SV_Api
 * @subpackage SV_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SV_Api
 * @subpackage SV_Api/includes
 * @author     Bellweather Agency <dan@bellweather.agency>
 */
class SV_Api {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      SV_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'SV_Api_API_VERSION' ) ) {
            $this->version = SV_Api_API_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'sv-api';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - SV_Api_Loader. Orchestrates the hooks of the plugin.
     * - SV_Api_i18n. Defines internationalization functionality.
     * - SV_Api_Admin. Defines all hooks for the admin area.
     * - SV_Api_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sv-api-loader.php';

        /**
         * This file contains BW helper functions
         *
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/sv-api-functions.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sv-api-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sv-api-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sv-api-public.php';

        $this->loader = new SV_Api_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the SV_Api_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new SV_Api_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new SV_Api_Admin( $this->get_plugin_name(), $this->get_version() );
        $plugin_settings = new SV_Api_Admin_Settings( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_management_page' );
        $this->loader->add_action( 'admin_menu', $plugin_settings, 'setup_plugin_options_menu' );
        $this->loader->add_action( 'admin_init', $plugin_settings, 'initialize_settings_options' );

        // $this->loader->add_action( 'init', $plugin_admin, 'custom_post_types' );
        $this->loader->add_action( 'init', $plugin_admin, 'add_acf_fields' );
        $this->loader->add_action( 'init', $plugin_admin, 'add_cpts' );

        $this->loader->add_action( 'wp_ajax_run_import', $plugin_admin, 'run_bulk_listings' );
        $this->loader->add_action( 'wp_ajax_run_events_import', $plugin_admin, 'run_bulk_events' );
        $this->loader->add_action( 'wp_run_import_CRON', $plugin_admin, 'run_bulk_listings_CRON' );
        $this->loader->add_action( 'wp_run_events_import_CRON', $plugin_admin, 'run_bulk_events_CRON' );

        $this->loader->add_action( 'wp_ajax_run_coupons_import', $plugin_admin, 'run_bulk_coupons' );
        $this->loader->add_action( 'wp_ajax_run_single_listing_import', $plugin_admin, 'run_single_listing_import' );
        $this->loader->add_action( 'wp_ajax_create_new_post_from_svid', $plugin_admin, 'create_new_post_from_svid' );
        $this->loader->add_action( 'wp_ajax_kill_cron', $plugin_admin, 'kill_cron' );

        $this->loader->add_action('run_coupons_import_by_page', $plugin_admin, 'run_coupons_import_by_page');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new SV_Api_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    SV_Api_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Pull the feed and parse it, creating all necessary data, etc.
     *
     * @since  1.0.0
     */
    public function pull_posts() {

    }

}
