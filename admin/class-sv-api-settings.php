<?php

/**
 * The settings of the plugin.
 *
 * @link       https://bellweather.agency/
 * @since      1.0.0
 *
 * @package    SV_Api
 * @subpackage SV_Api/admin
 */

/**
 * Class SV_Api_Admin_Settings
 *
 */
class SV_Api_Admin_Settings {

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
    }

    /**
     * This function introduces the theme options into the 'Appearance' menu and into a top-level
     * 'SV API' menu.
     */
    public function setup_plugin_options_menu() {

        //Add the menu to the Plugins set of menu items
        add_plugins_page(
            'SV API', 					// The title to be displayed in the browser window for this page.
            'SV API',					// The text to be displayed for this menu item
            'manage_options',					// Which type of users can see this menu item
            $this->plugin_name,			// The unique ID - that is, the slug - for this menu item
            array( $this, 'render_settings_page_content')				// The name of the function to call when rendering this menu's page
        );

    }

    /**
     * Provide default values for the Settings Options.
     *
     * @return array
     */
    public function default_settings_options() {

        $defaults = array(
            'api_url'		=>	'',
            'api_username'		=>	'',
            'api_password'	=>	'',
        );

        return  $defaults;

    }

    /**
     * Provide default values for the Log Data.
     *
     * @return array
     */
    public function default_log_data() {

        $defaults = array(
            'events_import_log'				=> '',
            'listings_import_log'			=> '',
            'events_import_folder'			=> plugin_dir_path( __FILE__ ) . 'logs/event_logs/',
            'listings_import_folder'		=> plugin_dir_path( __FILE__ ) . 'logs/listing_logs/',
            'select_events_import_log' 		=> '',
            'select_listings_import_log' 	=> '',
        );

        return  $defaults;

    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     */
    public function render_settings_page_content( $active_tab = '' ) {
?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">

            <h2><?php _e( 'SV API', $this->plugin_name ); ?></h2>
<?php
        settings_errors();

        if( isset( $_GET[ 'tab' ] ) ):
            $active_tab = $_GET[ 'tab' ];
        elseif( $active_tab == 'settings_options' ):
            $active_tab = 'settings_options';
        elseif( $active_tab == 'logs' ):
            $active_tab = 'logs';	
        else:
        $active_tab = 'status';
endif;
?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=sv-api&tab=status" class="nav-tab <?php echo $active_tab == 'status' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Status', $this->plugin_name ); ?></a>
                <a href="?page=sv-api&tab=settings_options" class="nav-tab <?php echo $active_tab == 'settings_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'API Settings', $this->plugin_name ); ?></a>
                <a href="?page=sv-api&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?php _e( 'API Logs', $this->plugin_name ); ?></a>
            </h2>
<?php

if( $active_tab == 'status' ):
    include plugin_dir_path( __FILE__ ) . 'views/status/index.php';
elseif ($active_tab == 'logs'):
    echo '<form action="options.php" method="post">';
settings_fields( 'sv_api_logs' );
do_settings_sections( 'sv_api_logs' );
submit_button('Select Logs');
echo '</form>';
else:
echo '<form action="options.php" method="post">';
settings_fields( 'sv_api_setting_options' );
do_settings_sections( 'sv_api_setting_options' );
submit_button();
echo '</form>';
endif;

?>

        </div><!-- /.wrap -->
<?php
    }

    /**
     * Initializes the theme's display options page by registering the Sections,
     * Fields, and Settings.
     *
     * This function is registered with the 'admin_init' hook.
     */
    public function initialize_settings_options() {

        // If the theme options don't exist, create them.
        if( false == get_option( 'sv_api_setting_options' ) ) {
            $default_array = $this->default_settings_options();
            update_option( 'sv_api_setting_options', $default_array );
        }

        add_settings_section(
            'sv_api_settings_section',			            	// ID used to identify this section and with which to register options
            __( 'SV API Settings', $this->plugin_name ),		// Title to be displayed on the administration page
            array( $this, 'sv_api_options_callback'),	    	// Callback used to render the description of the section
            'sv_api_setting_options'		            // Page on which to add this section of options
        );

        add_settings_field(
            'api_url',
            'Listings API URL',
            array( $this, 'api_url_callback'),
            'sv_api_setting_options',
            'sv_api_settings_section'
        );

        add_settings_field(
            'api_username',
            'Listings API Username',
            array( $this, 'api_username_callback'),
            'sv_api_setting_options',
            'sv_api_settings_section'
        );

        add_settings_field(
            'api_password',
            'Listings API Password',
            array( $this, 'api_password_callback'),
            'sv_api_setting_options',
            'sv_api_settings_section'
        );

        add_settings_field(
            'events_api_url',
            'Events API URL',
            array( $this, 'events_api_url_callback'),
            'sv_api_setting_options',
            'sv_api_settings_section'
        );

        add_settings_field(
            'events_api_key',
            'Events API Key',
            array( $this, 'events_api_key_callback'),
            'sv_api_setting_options',
            'sv_api_settings_section'
        );

        register_setting(
            'sv_api_setting_options',
            'sv_api_setting_options',
            array( $this, 'sanitize_setting_options')
        );

        if( false == get_option( 'sv_api_logs' ) ) {
            $default_array = $this->default_log_data();
            update_option( 'sv_api_logs', $default_array );
        }

        add_settings_section(
            'sv_api_logs_section',			            	// ID used to identify this section and with which to register options
            __( 'SV API Logs', $this->plugin_name ),		// Title to be displayed on the administration page
            array( $this, 'sv_api_logs_callback'),	    	// Callback used to render the description of the section
            'sv_api_logs'		         		// Page on which to add this section of options
        );

        add_settings_field(
            'event_imports_folder',
            'Event Imports Folder',
            array( $this, 'event_imports_folder_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );
        add_settings_field(
            'select_events_import_log',
            'Select Events Import Log',
            array( $this, 'select_events_import_log_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );
        add_settings_field(
            'events_import_log',
            'Events Import Log',
            array( $this, 'events_import_log_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );

        add_settings_field(
            'listing_imports_folder',
            'Listing Imports Folder',
            array( $this, 'listing_imports_folder_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );
        add_settings_field(
            'select_listings_import_log',
            'Select listings Import Log',
            array( $this, 'select_listings_import_log_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );
        add_settings_field(
            'listings_import_log',
            'Listings Import Log',
            array( $this, 'listings_import_log_callback'),
            'sv_api_logs',
            'sv_api_logs_section'
        );

        register_setting(
            'sv_api_logs',
            'sv_api_logs',
            array( $this, 'sanitize_setting_options')
        );

    }

    public function api_url_callback() {
        $options = get_option( 'sv_api_setting_options' );

        $url = '';
        if( isset( $options['api_url'] ) ) {
            $url = esc_url( $options['api_url'] );
        }
        echo '<input type="text" id="api_url" name="sv_api_setting_options[api_url]" value="' . $url . '" />';
    }

    public function api_username_callback() {
        $options = get_option( 'sv_api_setting_options' );

        echo '<input type="text" id="api_username" name="sv_api_setting_options[api_username]" value="' . $options['api_username'] . '" />';
    }

    public function api_password_callback() {
        $options = get_option( 'sv_api_setting_options' );
        echo '<input type="text" id="api_password" name="sv_api_setting_options[api_password]" value="' . $options['api_password'] . '" />';
    }

    public function events_api_url_callback() {
        $options = get_option( 'sv_api_setting_options' );

        $url = '';
        if( isset( $options['events_api_url'] ) ) {
            $url = esc_url( $options['events_api_url'] );
        }
        echo '<input type="text" id="events_api_url" name="sv_api_setting_options[events_api_url]" value="' . $url . '" />';
    }

    public function events_api_key_callback() {
        $options = get_option( 'sv_api_setting_options' );
        echo '<input type="text" id="events_api_key" name="sv_api_setting_options[events_api_key]" value="' . ($options['events_api_key'] ?? '') . '" />';
    }

    public function event_imports_folder_callback() {
        $options = get_option( 'sv_api_logs' );
        echo '<input class="hidden" type="text" id="events_import_folder" name="sv_api_logs[events_import_folder]" value="' . ($options['events_import_folder'] ?? '') . '" />';
        echo "<i>" . ($options['events_import_folder'] ?? '') . "</i>";
    }

    public function select_events_import_log_callback() {
        $options = get_option( 'sv_api_logs' );
        $log_folder = $options['events_import_folder'];

        $files = '';
        if (is_dir($log_folder)) {
            $files = array_diff(scandir($log_folder), array('.', '..'));
        }

        if ( !is_array($files) ) {
            echo '<input class="hidden" type="text" id="select_events_import_log" name="sv_api_logs[select_events_import_log]" value="" />';
            echo "There are no files in the log folder. Please run an import to generate a log.";
        }
        else {
            echo "<select id='select_events_import_log' name='sv_api_logs[select_events_import_log]'>";
            foreach ($files as $file) {
                if ($file == $options['select_events_import_log']) {
                    echo '<option selected value="'.$file.'">'.$this->prettyLogDisplay($file).'</option>';
                }
                else {
                    echo '<option value="'.$file.'">'.$this->prettyLogDisplay($file).'</option>';
                }
            }
            echo "</select>";
        }
    }

    public function events_import_log_callback() {
        $options = get_option( 'sv_api_logs' );
        $log_folder = $options['events_import_folder'];
        $log_file = $options['select_events_import_log'];

        $last_events_import = $log_folder.$log_file;

        echo "<div class='logWrapper'>";
        if ( file_exists($last_events_import) ) {
            echo nl2br(file_get_contents($last_events_import));
        }
        elseif ($last_events_import) {
            echo "The saved log file: <br><br><span style='color:green;'>".$last_events_import."</span><br><br>does not exist. Try selecting a new log from above.";
        }
        else {
            echo "No recent logs are available.";
        }
        echo "</div>";
    }


    public function listing_imports_folder_callback() {
        $options = get_option( 'sv_api_logs' );
        echo '<input class="hidden" type="text" id="listings_import_folder" name="sv_api_logs[listings_import_folder]" value="' . $options['listings_import_folder'] . '" />';
        echo "<i>".$options['listings_import_folder']."</i>";
    }

    public function select_listings_import_log_callback() {

        $options = get_option( 'sv_api_logs' );
        $log_folder = $options['listings_import_folder'];

        $files = '';
        if (is_dir($log_folder)) {
            $files = array_diff(scandir($log_folder), array('.', '..'));
        }

        if ( !is_array($files) ) {
            echo '<input class="hidden" type="text" id="select_listings_import_log" name="sv_api_logs[select_listings_import_log]" value="" />';
            echo "There are no files in the log folder. Please run an import to generate a log.";
        }
        else {
            echo "<select id='select_listings_import_log' name='sv_api_logs[select_listings_import_log]'>";
            foreach ($files as $file) {
                if ($file == $options['select_listings_import_log']) {
                    echo '<option selected value="'.$file.'">'.$this->prettyLogDisplay($file).'</option>';
                }
                else {
                    echo '<option value="'.$file.'">'.$this->prettyLogDisplay($file).'</option>';
                }
            }
            echo "</select>";
        }
    }

    public function listings_import_log_callback() {
        $options = get_option( 'sv_api_logs' );
        $log_folder = $options['listings_import_folder'];
        $log_file = $options['select_listings_import_log'];

        $last_listings_import = $log_folder.$log_file;
        echo "<div class='logWrapper'>";
        if (file_exists($last_listings_import)) {
            echo nl2br(file_get_contents($last_listings_import));
        }
        elseif ($last_listings_import) {
            echo "The saved log file: <br><br><span style='color:green;'>".$last_listings_import."</span><br><br>does not exist. Try selecting a new log from above.";
        }
        else {
            echo "No recent logs are available.";
        }
        echo "</div>";
    }

    public function sanitize_setting_options( $input ) {

        // Define the array for the updated options
        $output = array();

        // Loop through each of the options sanitizing the data
        foreach( $input as $key => $val ) {

            if( isset ( $input[$key] ) ) {
                $output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
            } // end if

        } // end foreach

        // Return the new collection
        return apply_filters( 'sanitize_setting_options', $output, $input );

    } // end sanitize_setting_options

    public function sv_api_options_callback() {
        // $options = get_option('sv_api_setting_options');
        // var_dump($options);
        echo '<p>' . __( 'Provide your API connection credentials.', $this->plugin_name ) . '</p>';
    } // end general_options_callback

    public function sv_api_logs_callback() {
        // $options = get_option('sv_api_setting_options');
        // var_dump($options);
        echo '<p>' . __( 'View the most recent import logs.', $this->plugin_name ) . '</p>';
    } // end general_options_callback

    public function prettyLogDisplay($file) {
        $date = strtok($file, '_');
        $formatDate = substr($date, 4, 2)."/".substr($date, 6, 2)."/".substr($date, 0, 4);
        return $formatDate;
    }
}

/* vim: set ts=4 sw=4 sts=4 et : */
