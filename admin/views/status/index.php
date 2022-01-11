<?php
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
    else:
    ?>
        <div class="notice notice-error inline" style="margin-top: 2em;"><p>Please enter all Listings <a href="?page=sv-api&tab=settings_options">API Settings</a></p></div>
    <?php
    endif;
    ?>

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
?>