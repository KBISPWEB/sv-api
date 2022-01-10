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
					$response = '';

					$check_listings_settings = check_listings_settings();

					$last_run = get_option( 'sv_api_last_run' );
					$method = get_option( 'sv_api_method' ) ?  ' (' . get_option( 'sv_api_method' ) . ')' : '';
					$method = ''; // TODO REMOVE

					$failure_message 	= get_option( 'sv_api_failure_message' );
					$failure_detail 	= get_option( 'sv_api_failure_detail' );

					$failure_check 		= get_option( 'sv_api_failure' );
					$failure_last_run 	= get_option( 'sv_api_failure_last_run' );
					$failure_last_run_msg = $failure_last_run ? ' <span style="color: #c7254e; font-style: normal; font-size: 0.9em;">with errors: some Listings may not have been added.</span>' : '';
					$is_success = $failure_check == 'no' ? true : false;
					$pull_success_verdict = $is_success ? '<strong style="color: #46b450">Success</strong>' .$failure_last_run_msg : '<p style="color: #dc3232"><strong>FAILED:</strong> ' . $failure_message . ': ' . $failure_detail . '</p>' ;
					
					$listings_results_count = get_option( 'sv_api_results_count' );
					$listings_processed 	= get_option( 'sv_api_listings_processed' );
					$listings_updated 		= get_option( 'sv_api_listings_updated' );
					$listings_errors 		= get_option( 'sv_api_listings_errors' );
					$listings_added 		= get_option( 'sv_api_listings_added' );

					$updated_links = '';

					// Events stuff
					$check_events_settings = check_events_settings();
					$events_last_run 	   = get_option( 'sv_api_last_run_events' );
					$events_method 		   = get_option( 'sv_api_event_method' ) ?  ' (' . get_option( 'sv_api_event_method' ) . ')' : '';

					$events_failed 				= get_option( 'sv_api_events_failure' );
					$events_failure_message 	= get_option( 'sv_api_events_failure_message' );
					$events_success_verdict 	= $events_failed ? '<p style="color: #dc3232"><strong>FAILED:</strong> ' . $events_failure_message . '</p>' : '<strong style="color: #46b450">Success</strong>';
					
					$events_results_count 	= get_option( 'sv_api_events_results_count' );
					$events_processed 		= get_option( 'sv_api_events_processed' );
					$events_updated 		= get_option( 'sv_api_events_updated' );
					$events_errors 			= get_option( 'sv_api_events_errors' );
					$events_added 			= get_option( 'sv_api_events_added' );

					$first_run = $last_run && $failure_check ? true : false; // check if import has been run before.

					?>
					<form action="options.php" method="post">
					<?php
					if($check_listings_settings):
					?>
						<table class="form-table listings-table" role="presentation">
							<tbody>
							<?php
							if( $first_run ) :
							?>
								<tr>
									<th scope="row-title">
										<strong>Listings Import Last Run:</strong>
									</th>
									<td>
										<em><?php echo $last_run . $method; ?></em>
									</td>
								</tr>
								<tr>
									<th scope="row-title">
										<strong>Listings Import Result:</strong>
									</th>
									<td>
										<em><?php echo $pull_success_verdict; ?></em>
									</td>
								</tr>
								<?php // if($is_success): ?>
								<tr>
									<th scope="row-title">
										<strong>Total listings on CRM:</strong><br /><small>(Includes duplicates)</small>
									</th>
									<td>
										<em><?php echo $listings_results_count; ?></em>
									</td>
								</tr>
								<tr>
									<th scope="row-title">
										<strong>Listings Processed:</strong>
										<br /><small>(Includes duplicates)</small>
									</th>
									<td>
										<em><?php echo $listings_processed; ?></em>
									</td>
								</tr>
								<tr>
								<tr>
									<th scope="row-title">
										<strong>Listings Added:</strong>
									</th>
									<td>
										<em><?php echo $listings_added; ?></em>
									</td>
								</tr>
								<tr>
									<th scope="row-title">
										<strong>Listings Updated:</strong>
									</th>
									<td>
										<em>
											<em><?php echo $listings_updated; ?></em>
										</em>
									</td>
								</tr>
								<tr>
									<th scope="row-title">
										<strong>Listings Errors:</strong>
									</th>
									<td>
										<em><?php echo $listings_errors; ?></em>
									</td>
								</tr>
								<tr>
								<?php
								// endif; // $is_success
							else:
							?>
							<tr>
								<th colspan="2">
									<div class="notice notice-warning inline"><p><em>Listings import has not been run yet.</em></p></div>
								</th>
							</tr>
							<?php
							endif; // $first_run
							?>
								<tr>
									<th colspan="2">
										<?php submit_button( 'Run Listings Import', 'primary run_now', 'submit', false ); ?>
									</th>
								</tr>
							</tbody>
						</table>
						<div class="run_import_status"></div>
					<?php
					else:
					?>
							<div class="notice notice-error inline" style="margin-top: 2em;"><p>Please enter all Listings <a href="?page=sv-api&tab=settings_options">API Settings</a></p></div>
					<?php
					endif;
					?>

					<table style="display: none;">
					  <tbody>
					  	<tr>
			  				<th colspan="2">
			  					<div style="margin-bottom:1em;margin-top:1em;">
			  						<?php submit_button( 'Kill Active Cron Job', 'primary kill_cron', 'kill_cron', false ); ?>
										<div class="kill_cron_status"></div>
			  					</div>
			  				</th>
					  	</tr>
					  </tbody>
					</table>
					<hr />
					<table>
					  <tbody>
					  	<tr>
					  		<th cols="2">
					  			<div style="display:flex;width:100%;margin-bottom:.75em;margin-top:1.5em;">
					  				<input checked class="my-radio" type="radio" id="wp" name="id_type" value="wp">
					  				<label style="margin-left:5px;" for="wp">WordPress ID</label><br>
					  			</div>
					  			<div style="display:flex;width:100%">
					  				<input class="my-radio" type="radio" id="sv" name="id_type" value="sv">
					  				<label style="margin-left:5px;" for="sv">SimpleView ID</label><br>
					  			</div>
					  		</th>
					  	</tr>
					    <tr>
					    	<th cols="2">
					    			<div style="margin-top:1em;width:100%">
					    				<input
					    					style="width:100%;"
					    					type="number" id="listing_id" name="listing_id" min="100"
					    					placeholder="Listing ID"
					    				>
					    			</div>
					    	</th>
					    </tr>
					    <tr>
					    	<th cols="2">
					    		<div style="width:100%;display:flex;">
						    	  <div
						    	  	id="single-listings-alert-text"
						    	  	class="alert-text"
						    	  	style="text-align:left;width:100%;margin-left: 3px;"
						    	  >
						    	  	<em><p style="margin:0px;">Enter a properly formatted ID.</p></em>
						    	  </div>
					    	  </div>
					    	</th>
					    </tr>
					    <tr>
					    	<th cols="2">
					    		<div style="width:100%;display:flex;">
						    	  <input
						    	  	style="margin-top:1.5em"
						    	  	type="submit"
						    	  	name="submit_single_listing"
						    	  	id="submit_single_listing"
						    	  	class="button button-primary run_now_single_listing"
						    	  	value="Run Single Listing Import"
						    	  	disabled=true
						    	  >
						    	  </input>
					    	  </div>
					    	</th>
					    </tr>
					  </tbody>
					</table>
					<div id="single_listing_ajaxLoader"></div>
					<div class="run_import_single_listing_status" style="margin-bottom:2em;"></div>

					<div id="create_new_listing_prompt" class="hidden">
						<div id="create_new_listing_prompt_box">
							<div class="create_new_message">
								There is no wordpress post associated with SimpleView ID <span id="sv_id_display"></span>.<br>
								Clicking "Yes" will <em>create a new post</em> in wordpress.<br><br>
								If you are seeking to update an existing post, find that listing and either use the wordpress ID,
								or check the field <em>listing_id</em> for the correct value.
							</div>
							<div style="margin-top: 20px;">
								Would you like to create a new listings post?
							</div>
							<div style="display: flex;width: 100%;margin-top: 10px;">
								<div
									style="margin-right: 15px;"
									class="yes_create_new_post button button-primary"
									data-svidToFetch
								>
									Yes.
								</div>
								<div class="do_not_create_new_post button button-primary">
									No.
								</div>
							</div>
						</div>
					</div>

					<?php
					if($check_events_settings):
					?>
						<hr />
						<table class="form-table events-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row-title">
										<strong>Events Import Last Run:</strong>
									</th>
									<td>
										<em><?php echo $events_last_run . $events_method; ?></em>
									</td>
								</tr>
								<tr>
									<th scope="row-title">
										<strong>Events Import Result:</strong>
									</th>
									<td>
										<em><?php echo $events_success_verdict; ?></em>
									</td>
								</tr>
								<?php if(!$events_failed): ?>
									<tr>
										<th scope="row-title">
											<strong>Total events on CRM:</strong>
										</th>
										<td>
											<em><?php echo $events_results_count; ?></em>
										</td>
									</tr>
									<tr>
										<th scope="row-title">
											<strong>Events Processed:</strong>
										</th>
										<td>
											<em><?php echo $events_processed; ?></em>
										</td>
									</tr>
									<tr>
										<th scope="row-title">
											<strong>Events Added:</strong>
										</th>
										<td>
											<em><?php echo $events_added; ?></em>
										</td>
									</tr>
									<tr>
										<th scope="row-title">
											<strong>Events Updated:</strong>
										</th>
										<td>
											<em><?php echo $events_updated; ?></em>
										</td>
									</tr>
									<tr>
										<th scope="row-title">
											<strong>Event Errors:</strong>
										</th>
										<td>
											<em><?php echo $events_errors; ?></em>
										</td>
									</tr>
								<?php endif; // $events_failed ?>
								<tr>
									<th cols="2">
										<?php submit_button( 'Run Events Import', 'primary run_now_events', 'submit_events', false ); ?>
									</th>
								</tr>
							</tbody>
						</table>
						<hr />
						<?php
					else:
					?>
						<div class="notice notice-error inline" style="margin-top: 2em;"><p>Please enter all Events <a href="?page=sv-api&tab=settings_options">API Settings</a></p></div>
					<?php
					endif; // $check_events_settings
						?>
						<div class="run_import_event_status"></div>

						<br>
						<table>
							<tbody>
								<tr>
									<th cols="2">
										<?php
											submit_button( 'Run Coupons Import', 'primary run_now_coupons', 'submit_coupons', false );
										?>
									</th>
								</tr>
							</tbody>
						</table>
						<div id="ajaxLoader"></div>
						<div class="run_import_coupons_status"></div>

					</form>
					<?php


					if( !$is_success && $first_run):
						echo '<div class="error"><p>There were errors connecting to the Simpleview API. Please check the <a href="?page=sv-api&tab=settings_options">API Settings</a> and try again.</p></div>';
					endif;

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
  		echo '<input type="text" id="events_api_key" name="sv_api_setting_options[events_api_key]" value="' . $options['events_api_key'] . '" />';
  	}

	public function event_imports_folder_callback() {
		$options = get_option( 'sv_api_logs' );
		echo '<input class="hidden" type="text" id="events_import_folder" name="sv_api_logs[events_import_folder]" value="' . $options['events_import_folder'] . '" />';
		echo "<i>".$options['events_import_folder']."</i>";
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
