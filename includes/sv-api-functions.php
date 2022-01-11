<?php


function create_new_listing($response){
  if ($response['REQUESTSTATUS']['HASERRORS']) {
    return [
      false, // status
      "Error from SV: ".$response['REQUESTSTATUS']['ERRORS']['ITEM']['DETAIL'], //message
      false, // link
      false  // pid
    ];
  }
  else {
    $listing                  = $response['LISTING'];

    $images_response          = isset( $listing['IMAGES']['ITEM'] ) ? $listing['IMAGES']['ITEM'] : false;
    $hr_response              = isset( $listing['HIGHRESIMAGE']['ITEM'] ) ? $listing['HIGHRESIMAGE']['ITEM'] : false;
    $last_updated             = $listing['LASTUPDATED'];
    $svid                     = $listing['LISTINGID'];
    $company                  = !empty( $listing['COMPANY'] ) ? $listing['COMPANY'] : false;

    if ($company) { // if company name missing, abort

      $standard_fields = grab_fields($listing);
      $listing_type_id = process_membership_info($listing['TypeofMember']);
      $images          = process_api_images($images_response, $hr_response);

      // Create the post
      $post = array(
        'post_title'    => $standard_fields['sort_company'],
        'post_author'   => 1,
        'post_content'  => $standard_fields['description'],
        'post_status'   => 'publish',
        'post_type'     => 'listings'
      );
      $pid = wp_insert_post($post, true);  // Pass the value of $post to WordPress the insert function

      // populate amenities textarea
      $tag_to_tab = get_amenities_info();
      $amenities_string = process_amenities($listing['AMENITIES']['ITEM'], $tag_to_tab);
      update_field("amenities", $amenities_string, $pid);

      // add post terms
      wp_set_object_terms($pid, $standard_fields['post_cats'], 'category');

      // populate the post meta data
      update_standard_fields($pid, $standard_fields);

      if ( strtolower($standard_fields['rank']) === "premium" ) {
        update_premium_meta($pid, $listing);
      }
      elseif ($listing_type_id) {
        reset_listing_type($pid, $listing_type_id);
      }

      BW_upload_images($pid, $images[0], $images[1]);

      return [
        true,
        "WP Post ".$pid." created.",
        get_the_permalink($pid),
        $pid
      ];
    }
    else {
      return [
        false,
        "Listing Not Updated. Company name in SV API Response was empty.",
        false,
        false
      ];
    }
  }

  return [
    false,
    "Attempt to create post failed.",
    false,
    false
  ];
}

function update_listing($response, $pid) {
  $listing                  = $response['LISTING'];

  $images_response          = isset( $listing['IMAGES']['ITEM'] ) ? $listing['IMAGES']['ITEM'] : false;
  $hr_response              = isset( $listing['HIGHRESIMAGE']['ITEM'] ) ? $listing['HIGHRESIMAGE']['ITEM'] : false;
  $last_updated             = $listing['LASTUPDATED'];
  $svid                     = $listing['LISTINGID'];
  $company                  = !empty( $listing['COMPANY'] ) ? $listing['COMPANY'] : false;

  if ($company) { // if company name missing, abort

    // TODO: will need to avoid running this each time in bulk update
    if (!isset($existing_companies)) {
      $existing_companies = existing_companies();
    }
    if (!isset($existing_listing_ids)) {
      $existing_listing_ids = existing_listing_ids();
    }

    $standard_fields = grab_fields($listing);
    $listing_type_id = process_membership_info($listing['TypeofMember']);

    $images = process_api_images($images_response, $hr_response);

    $type_name = $listing['TYPENAME'];

    if( in_array($svid, $existing_listing_ids) && ($type_name == "Website") ) {
      // Get post data
      $post_data = get_posts( [
        'post_type' => 'listings',
        'meta_key'   => 'listing_id',
        'meta_value' => $svid,
        'posts_per_page' => 1,
      ] );


      // TODO: Make error
      if ($pid !== intval($post_data[0]->ID) ) {
        return [
          false,
          "Post ID and SVID do not match. There may be duplicate listings.",
          false,
          false
        ];
      }

      // Get latest modification date
      $post_modified  = $post_data[0]->post_modified;

      // populate amenities textarea
      $tag_to_tab = get_amenities_info();
      $amenities_string = process_amenities($listing['AMENITIES']['ITEM'], $tag_to_tab);
      update_field("amenities", $amenities_string, $pid);

      // add post terms
      wp_set_object_terms($pid, $standard_fields['post_cats'], 'category');

      if ( strtolower($standard_fields['rank']) === "premium" ) {
        update_premium_meta($pid, $listing);
      }
      elseif ($listing_type_id) {
        reset_listing_type($pid, $listing_type_id);
      }

      BW_upload_images($pid, $images[0], $images[1]);

      // Update if we have pid
      if( $pid ){
        $time = current_time('mysql');
        $post_updates = array(
          'ID'            => $pid,
          'post_title'    => $standard_fields['sort_company'],
          'post_content'  => $standard_fields['description'],
          'post_modified'     => $time,
          'post_modified_gmt' => get_gmt_from_date( $time ),
        );
        $pid = wp_update_post($post_updates, true);

        update_standard_fields($pid, $standard_fields);

        return [
          true,
          "WP Post ".$pid." updated.",
          get_the_permalink($pid),
          $pid
        ];
      }

    }
    else {
      // return that listing does not exist
    }

    return [
      false,
      "Listing Not Updated.",
      false,
      false
    ];
  }
  else {
    return [
      false,
      "Listing Not Updated. Company name in SV API Response was empty.",
      false,
      false
    ];
  }
}

function update_standard_fields($pid, $standard_fields) {
  update_field('listing_id', $standard_fields['listing_id'], $pid);
  update_field('address', $standard_fields['address'], $pid);
  update_field('alternate', $standard_fields['alternate'], $pid);
  update_field('company', $standard_fields['company'], $pid);
  update_field('contact', $standard_fields['contact'], $pid);
  update_field('email', $standard_fields['email'], $pid);
  update_field('facebook', $standard_fields['facebook'], $pid);
  update_field('fax', $standard_fields['fax'], $pid);
  update_field('instagram', $standard_fields['instagram'], $pid);
  update_field('map_coordinates', $standard_fields['map_coordinates'], $pid);
  update_field('phone', $standard_fields['phone'], $pid);
  update_field('rank', $standard_fields['rank'], $pid);
  update_field('region', $standard_fields['region'], $pid);
  update_field('type_of_member', $standard_fields['type_of_member'], $pid);
  update_field('search_keywords', $standard_fields['search_keywords'], $pid);
  update_field('sort_company', $standard_fields['sort_company'], $pid);
  update_field('tollfree', $standard_fields['tollfree'], $pid);
  update_field('twitter', $standard_fields['twitter'], $pid);
  update_field('wct_id', $standard_fields['wct_id'], $pid);
  update_field('website', $standard_fields['website'], $pid);
  update_field('youtube', $standard_fields['youtube'], $pid);
}

function reset_listing_type($pid, $listing_type_id) {
  wp_set_object_terms( $pid, intval( $listing_type_id ), 'listing_type' );
}

function process_api_images($response, $hr_response) {
  $response = handle_array_anomalies($response);
  $images = array();

  $hr_response = handle_array_anomalies($hr_response);
  $hr_images = array();

  if ($response !== false) {
    foreach ($response as $image) {
      if (isset( $image['TYPEID'] )) {
        if ($image['TYPEID'] == 2 || $image['TYPEID'] == 1) { //TYPEID for standard img
          $image_id                   = $image['MEDIAID'];
          $images[$image_id]          = array();
          $images[$image_id]['URL']   = $image['IMGPATH'].$image['MEDIAFILE'];
          $images[$image_id]['TITLE'] = $image['MEDIANAME'];

          if (!is_array($image['MEDIADESC'])) { //rimages do not have desc often
            $images[$image_id]['DESC']  = $image['MEDIADESC'];
          }
          else {
            $images[$image_id]['DESC']  = "";
          }
        }
        elseif ($image['TYPEID'] == 4) { //TYPEID for high-res img
          $hr_id = $image['MEDIAID'];
          $hr_images[$hr_id] = array();
          $hr_images[$hr_id]['TITLE'] = $image['MEDIANAME'];
          $hr_images[$hr_id]['DESC']  = $image['MEDIADESC'];
          $hr_images[$hr_id]['URL']   = "";
        }
      }
    }
  }
  else {
    $images = false;
  }

  if ($hr_response !== false) {
    if ( isset($hr_response[0]) ) {
      foreach ($hr_response as $hr_image) {
        if (isset( $hr_image['MEDIAID'] )) {
          $hr_id = $hr_image['MEDIAID'];
          if ( isset($hr_images[$hr_id]) ) {
            if (!(is_array($hr_image['PATH'])) && !(is_array($hr_image['HIGHRESIMAGE'])) ) {
              $hr_images[$hr_id]['URL']   = $hr_image['PATH'].$hr_image['HIGHRESIMAGE'];
            }
            else {
              $hr_images[$hr_id]['URL'] = "";
            }
          }
          else { //FALLBACK
            $hr_id = $hr_image['MEDIAID'];
            $hr_images[$hr_id]          = array();
            $hr_images[$hr_id]['TITLE'] = "";
            $hr_images[$hr_id]['DESC']  = "No Descipton";
            $hr_images[$hr_id]['URL']   = $hr_image['PATH'].$hr_image['HIGHRESIMAGE'];
          }
        }
      }
    }
    else { // The array is not nested

      if (isset( $hr_images_response['MEDIAID'] )) {
        $hr_id = $hr_images_response['MEDIAID'];
        if ( isset($hr_images[$hr_id]) ) {
          if (!(is_array($hr_images_response['PATH'])) && !(is_array($hr_images_response['HIGHRESIMAGE'])) ) {
            $hr_images[$hr_id]['URL']   = $hr_images_response['PATH'].$hr_images_response['HIGHRESIMAGE'];
          }
          else {
            $hr_images[$hr_id]['URL'] = "";
          }
        }
        else { //FALLBACK
          $hr_id = $hr_images_response['MEDIAID'];
          $hr_images[$hr_id]          = array();
          $hr_images[$hr_id]['TITLE'] = "";
          $hr_images[$hr_id]['DESC']  = "No Descipton";
          $hr_images[$hr_id]['URL']   = $hr_images_response['PATH'].$hr_images_response['HIGHRESIMAGE'];
        }
      }
    }
  }
  else {
    $hr_images = false;
  }

  return [$images, $hr_images];
}

function process_membership_info($type) {

  if ($type == "Members") {
    $listing_type_id_arr = term_exists( 'members', 'listing_type' );
    $listing_type_id = $listing_type_id_arr['term_id'];
  }
  elseif ($type == "Non-Members") {
    $listing_type_id_arr = term_exists( 'non-members', 'listing_type' );
    $listing_type_id = $listing_type_id_arr['term_id'];
  }
  elseif ($type == "Premium Members") {
    $listing_type_id_arr = term_exists( 'premium', 'listing_type' );
    $listing_type_id = $listing_type_id_arr['term_id'];
  }
  else {
    $listing_type_id = false;
  }

  return $listing_type_id;
}

function get_amenities_info() {
  $amenities_info_response = sv_api_connection('getListingAmenities', 1);
  $amenities_info = $amenities_info_response['AMENITIES']['AMENITY'];
  foreach ($amenities_info as $info) {
    $tab_id     = $info["AMENITYTABID"];
    $tab_name   = $info["AMENITYTABNAME"];

    if ( !(isset($tag_to_tab[$tab_id])) ) {
      $tag_to_tab[$tab_id] = array($tab_name , array() ) ;
    }
  }

  return $tag_to_tab;
}

function process_amenities($amenities_response, $tag_to_tab) {

  $amenities = array();

  foreach ($amenities_response as $amenity) {
    if ( isset($amenity['AMENITYTABID']) ) {
      $amenityIndex = $amenity['AMENITYTABID'];
      if ( !is_array($amenity['VALUE']) && ($amenity['VALUE']!='0') && ($amenity['VALUE']!=0) ) {
        $tag_to_tab[$amenityIndex][1][$amenity['NAME']] = $amenity['VALUE'];
      }
    }
  }

  $amenities_string = "";
  foreach($tag_to_tab as $tab) {

    $tab_name = $tab[0];
    $tab_amenities = $tab[1];
    $amenities_pre_string = "";
    $amenities_list_string = "";
    $amenities_post_string = "";

    if ( count($tab_amenities) > 0 ) {
      foreach($tab_amenities as $name => $value){
        if ($value == '1'){
          $amenities_list_string .= "<li>".$name."</li>";
        }
        else {
          $amenities_list_string .= "<li>".$name.": ".$value."</li>";
        }
      }
    }
    if ( strlen($amenities_list_string) > 0 ) {
      $amenities_pre_string = "<h2><strong>".$tab_name."</strong></h2>";
      $amenities_pre_string .= "<ul>";
      $amenities_post_string = "</ul>";
      $amenities_string .= $amenities_pre_string.$amenities_list_string.$amenities_post_string;
    }
  }

  // Clear The Array
  foreach ($amenities_response as $amenity) {
    if ( isset($amenity['AMENITYTABID']) ) {
      $amenityIndex = $amenity['AMENITYTABID'];
      unset( $tag_to_tab[$amenityIndex][1] );
      $tag_to_tab[$amenityIndex][1] = array();
    }
  }

  return $amenities_string;
}

function grab_fields($listing){

  // Address //
  $address                = '';
  $address                .= !empty( $listing['ADDR1'] ) ? $listing['ADDR1'] : '';
  $address                .= !empty( $listing['ADDR2'] ) ? "\n" . $listing['ADDR2'] : '';
  $address                .= !empty( $listing['ADDR3'] ) ? "\n" . $listing['ADDR3'] : '';
  $city                   = !empty( $listing['CITY'] ) ? $listing['CITY'] : '';
  $state                  = !empty( $listing['STATE'] ) ? $listing['STATE'] : '';
  $zip                    = !empty( $listing['ZIP'] ) ? ' ' . $listing['ZIP'] : '';
  if( $city != '' && $state != ''):
    $city_state = $city . ', ' . $state;
  else:
    $city_state = $city . $state;
  endif;
  $address                .= "\n" . $city_state . $zip;

  $map_coordinates        = '';
  if( !empty( $listing['LATITUDE']) && !empty( $listing['LONGITUDE'] ) ):
    $map_coordinates        = $listing['LATITUDE'] . ',' . $listing['LONGITUDE'];
  endif;

  // General //

  $company                = !empty( $listing['COMPANY'] ) ? $listing['COMPANY'] : false;
  $phone                  = !empty( $listing['PHONE'] ) ? $listing['PHONE'] : '';
  $alternate              = !empty( $listing['ALTPHONE'] ) ? $listing['ALTPHONE'] : '';
  $tollfree               = !empty( $listing['TOLLFREE'] ) ? $listing['TOLLFREE'] : '';
  $fax                    = !empty( $listing['FAX'] ) ? $listing['FAX'] : '';
  $sort_company           = !empty( $listing['SORTCOMPANY'] ) ? $listing['SORTCOMPANY'] : $listing_id . ' Company Name Missing';
  $contact                = !empty( $listing['PRIMARYCONTACTFULLNAME'] ) ? $listing['PRIMARYCONTACTFULLNAME'] : '';
  $email                  = !empty( $listing['EMAIL'] ) ? $listing['EMAIL'] : '';
  $rank                   = !empty( $listing['RANKNAME'] ) ? $listing['RANKNAME'] : '';
  $region                 = !empty( $listing['REGION'] ) ? $listing['REGION'] : '';

  // TODO: is this LISTINGKEYWORDS or LISTING_KEYWORDS
  $search_keywords        = !empty( $listing['LISTINGKEYWORDS'] ) ? $listing['LISTINGKEYWORDS'] : '';

  
  $website                = !empty( $listing['WEBURL'] ) ? $listing['WEBURL'] : '';
  $description            = !empty( $listing['DESCRIPTION'] ) ? $listing['DESCRIPTION'] : '';

  $twitter                = '';
  $facebook               = '';
  $instagram              = '';
  $youtube                = '';

  $social_media = !empty( $listing['SOCIALMEDIA']['ITEM'] ) ? $listing['SOCIALMEDIA']['ITEM'] : '';
  // Handle social media URLS.
  // SV API Arrays differ if there is only 1 SOCIALMEDIA ITEM
  if( isset( $social_media[0] ) ):
    if( is_array( $social_media[0] ) ):
      foreach ($social_media as $network):
        $network_service = strtolower( $network['SERVICE'] );
        $$network_service = !empty( $network['VALUE'] ) ? $network['VALUE'] : '';
      endforeach;
    // Only 1 item
    else:
      $network_service = strtolower( $social_media['SERVICE'] );
      $$network_service = !empty( $social_media['VALUE'] ) ? $social_media['VALUE'] : '';
    endif;
  endif;

  // Categories TODO: Maybe Handle Seperately
  $post_cats              = array();
  $cat_name               = !empty( $listing['CATNAME'] ) ? $listing['CATNAME'] : '';
  $cat_slug               = reformCategorySlug($cat_name);
  $category               = addCategory($cat_name, $cat_slug);

  $subcat_name            = !empty( $listing['SUBCATNAME'] ) ? $listing['SUBCATNAME'] : '';
  if( $subcat_name != ''):
    $subcat_slug            = reformCategorySlug($subcat_name);
    $subcategory            = addCategory($subcat_name, $subcat_slug, $category);
  endif;

  array_push($post_cats, intval($category), intval($subcategory) );

  // TODO: does not exist on alx
  $ticket_link            = !empty( $listing['TICKETSLINK'] ) ? $listing['TICKETSLINK'] : '';
  $type_of_member         = !empty( $listing['TYPEOFMEMBER'] ) ? $listing['TYPEOFMEMBER'] : '';
  $wct_id                 = !empty( $listing['WCTID'] ) ? $listing['WCTID'] : '';
  $hours                  = !empty( $listing['HOURS'] ) ? $listing['HOURS'] : '';

  $fields = array();
  $fields['listing_id'] = $listing['LISTINGID'];
  $fields['company'] = $company;
  $fields['address'] = $address;
  $fields['map_coordinates'] = $map_coordinates;
  $fields['phone'] = $phone;
  $fields['alternate'] = $alternate;
  $fields['tollfree'] = $tollfree;
  $fields['fax'] = $fax;
  $fields['sort_company'] = $sort_company;
  $fields['contact'] = $contact;
  $fields['email'] = $email;
  $fields['hours'] = $hours;
  $fields['rank'] = $rank;
  $fields['region'] = $region;
  $fields['search_keywords'] = $search_keywords;
  $fields['ticket_link'] = $ticket_link;
  $fields['type_of_member'] = $type_of_member;
  $fields['wct_id'] = $wct_id;
  $fields['website'] = $website;
  $fields['description'] = $description;
  $fields['twitter'] = $twitter;
  $fields['facebook'] = $facebook;
  $fields['instagram'] = $instagram;
  $fields['youtube'] = $youtube;

  $fields['post_cats'] = $post_cats;
  $fields['category'] = $category;
  $fields['subcategory'] = $subcategory;

  return $fields;
}

/* ==========================================================================
Events
========================================================================== */

function update_event($event, $pid) {

  $description						= !empty( strval($event->description) ) ? strval($event->description) : '';
  $title 									= !empty( strval($event->title) ) ? strval($event->title) : '';
  $eventid                = !empty( strval($event->eventid) ) ? strval($event->eventid) : '';

  // error_log(print_r("Update Event: ".$pid, true));

  $fields = grab_event_fields($event);
  update_event_standard_fields($pid, $fields);
  wp_set_post_terms($pid, $fields['post_cats'], 'category');
  update_event_imgaes($pid, $event, $title);

  $post_data = array(
    'ID'            => $pid,
    'post_title'    => $title, 
    'post_content'  => $description
  );
  wp_update_post($post_data);

  return [
    true,
    "WP Post ".$pid." updated.",
    get_the_permalink($pid),
    $pid
  ];
}

function create_new_event($event) {
  $description						= !empty( strval($event->description) ) ? strval($event->description) : '';
  $title 									= !empty( strval($event->title) ) ? strval($event->title) : '';

  if ($title) {
    
    // Create the event post
    $post = array(
      'post_title'    => $title,
      'post_author'   => 1,
      'post_content'  => $description,
      'post_status'   => 'publish',
      'post_type'     => 'events'
    );
    $pid = wp_insert_post($post, true);  // Pass the value of $post to WordPress the insert function

    // error_log(print_r("Create Event: ".$pid, true));

    $fields = grab_event_fields($event);
    update_event_standard_fields($pid, $fields);
    wp_set_post_terms($pid, $fields['post_cats'], 'category');
    process_event_images($pid, $event, $title);

    return [
      true,
      "WP Post ".$pid." created.",
      get_the_permalink($pid),
      $pid
    ];

  }
  else {

    return [
      false, // status
      "Error. Event has no title.", //message
      false, // link
      false  // pid
    ];

  }

}

function update_event_imgaes($pid, $event, $title) {

  $image_list 						=  array();
  $event_images 					= ((array) $event->images );

  if ( isset( $event_images['image'] ) ) {

    $event_images = $event_images['image'];

    if (isset($event_images->mediafile)) {
      $image_list[] = strval($event_images->mediafile);
    }
    else {
      foreach($event_images as $index=>$image):
        $image_list[] = strval($image->mediafile);
      endforeach;
    }

  }
  
  $thumbnail_id = get_post_thumbnail_id($pid);
  $gallery = get_field('media', $pid);
  
  $added_featured = false;
  $mid = array();

  if (!$gallery) {
    foreach ($image_list as $image_url) {

      $id = saveImageToWP($image_url, $pid, $title, "_events");

      if (!$thumbnail_id && !$added_featured ) { // set first image to be thumbnail
        set_post_thumbnail($pid, $id);
        $added_featured = true;
      }

      array_push($mid, $id);
    }
    update_post_meta($pid, 'media', $mid);
  }
  else if (!$thumbnail_id) { // we have to replace the thumbnail from the gallery
    if ( isset($gallery[0]) ) {
      set_post_thumbnail($pid, $gallery[0]['ID']);
    }
  }
}

function process_event_images($pid, $event, $title) {

  $image_list 						=  array();
  $event_images 					= ((array) $event->images );

  if ( isset( $event_images['image'] ) ) {

    $event_images = $event_images['image'];

    if (isset($event_images->mediafile)) {
      $image_list[] = strval($event_images->mediafile);
    }
    else {
      foreach($event_images as $index=>$image):
        $image_list[] = strval($image->mediafile);
      endforeach;
    } 

  }

  $added_featured = false;
  $mid = array();
  foreach ($image_list as $image_url) {
  
    $id = saveImageToWP($image_url, $pid, $title, "_events");
  
    if (!$added_featured ) { // set first image to be thumbnail
      set_post_thumbnail($pid, $id);
      $added_featured = true;
    }
  
    array_push($mid, $id);
  }
  update_post_meta($pid, 'media', $mid);
}

function grab_event_fields($event) {

  $address 								= !empty( strval($event->address) ) ? strval($event->address) : '';
  $admission 							= !empty( strval($event->admission) ) ? strval($event->admission) : '';
  $city 									= !empty( strval($event->city) ) ? strval($event->city) : '';
  $contact 								= !empty( strval($event->contact) ) ? strval($event->contact) : '';
  $created 								= !empty( strval($event->created) ) ? strval($event->created) : '';
  $email 									= !empty( strval($event->email) ) ? strval($event->email) : '';
  $enddate 								= !empty( strval($event->enddate) ) ? date('Ymd', strtotime($event->enddate)) : '';
  $endtime 								= !empty( strval($event->endtime) ) ? strval($event->endtime) : '';

  // handle $eventdates
  $eventid                = !empty( strval($event->eventid) ) ? strval($event->eventid) : '';
  $eventdates_array     	= array();
  $event_eventdates  			= $event->eventdates;
  foreach ($event_eventdates as $eventdate):
    array_push($eventdates_array, strval($eventdate->eventdate) );
  endforeach;
  $eventdates 						= implode(",", $eventdates_array);

  $eventregion 						= !empty( strval($event->eventregion) ) ? strval($event->eventregion) : '';
  $eventtype 							= !empty( strval($event->eventtype) ) ? strval($event->eventtype) : '';
  $featured 							= !empty( strval($event->featured) ) ? strval($event->featured) : '';
  $hostlistingid 					= !empty( strval($event->hostlistingid) ) ? strval($event->hostlistingid) : '';
  $hostname 							= !empty( strval($event->hostname) ) ? strval($event->hostname) : '';
  $listingid 							= !empty( strval($event->listingid) ) ? strval($event->listingid) : '';
  $location 							= !empty( strval($event->location) ) ? strval($event->location) : '';

  $map_coordinates 				= '';
  if( !empty( $event->latitude ) && !empty( $event->longitude ) ):
    $map_coordinates 				= strval($event->latitude) . ',' . strval($event->longitude);
  endif;

  $mediafile 							= '';
  $neverexpire 						= !empty(strval( $event->neverexpire) ) ? strval($event->neverexpire) : '';
  $phone 									= !empty(strval( $event->phone) ) ? strval($event->phone) : '';
  $recurrence 						= !empty(strval( $event->recurrence) ) ? strval($event->recurrence) : '';
  $startdate 							= !empty(strval( $event->startdate) ) ? date('Ymd', strtotime($event->startdate)) : '';
  $starttime 							= !empty(strval( $event->starttime) ) ? strval($event->starttime) : '';
  $state 									= !empty(strval( $event->state) ) ? strval($event->state) : '';
  $times 									= !empty(strval( $event->times) ) ? strval($event->times) : '';
  $website 								= !empty(strval( $event->website) ) ? strval($event->website) : '';
  $zip 										= !empty(strval( $event->zip) ) ? strval($event->zip) : '';

  $fields = array();
  $fields['address'] = $address;
  $fields['admission'] = $admission;
  $fields['city'] = $city;
  $fields['contact'] = $contact;
  $fields['created'] = $created;
  $fields['email'] = $email;
  $fields['enddate'] = $enddate;
  $fields['endtime'] = $endtime;
  $fields['eventdates'] = $eventdates;
  $fields['eventid'] = $eventid;
  $fields['eventregion'] = $eventregion;
  $fields['eventtype'] = $eventtype;
  $fields['featured'] = $featured;
  $fields['hostlistingid'] = $hostlistingid;
  $fields['hostname'] = $hostname;
  $fields['listingid'] = $listingid;
  $fields['location'] = $location;
  $fields['map_coordinates'] = $map_coordinates;
  $fields['mediafile'] = $mediafile;
  $fields['neverexpire'] = $neverexpire;
  $fields['phone'] = $phone;
  $fields['recurrence'] = $recurrence;
  $fields['startdate'] = $startdate;
  $fields['starttime'] = $starttime;
  $fields['state'] = $state;
  $fields['times'] = $times;
  $fields['website'] = $website;
  $fields['zip'] = $zip;


  // handle categories
  $post_cats = array();
  $eventcategories				= !empty($event->eventcategories) ? $event->eventcategories : '';
  foreach ($eventcategories->eventcategory as $category):
    $cat_name 							= strval($category->categoryname);
    $cat_slug								= reformCategorySlug($cat_name);
    $category 							= addCategory($cat_name, $cat_slug);
    array_push( $post_cats, $category );
  endforeach;

  $fields['post_cats'] = $post_cats;
  $fields['category'] = $category;

  return $fields;
}

function update_event_standard_fields($pid, $standard_fields) {
  update_field('address', $standard_fields['address'], $pid);
  update_field('admission', $standard_fields['admission'], $pid);
  update_field('city', $standard_fields['city'], $pid);
  update_field('contact', $standard_fields['contact'], $pid);
  update_field('created', $standard_fields['created'], $pid);
  update_field('email', $standard_fields['email'], $pid);
  update_field('enddate', $standard_fields['enddate'], $pid);
  update_field('endtime', $standard_fields['endtime'], $pid);
  update_field('eventdates', $standard_fields['eventdates'], $pid);
  update_field('eventid', $standard_fields['eventid'], $pid);
  update_field('eventregion', $standard_fields['eventregion'], $pid);
  update_field('eventtype', $standard_fields['eventtype'], $pid);
  update_field('featured', $standard_fields['featured'], $pid);
  update_field('hostlistingid', $standard_fields['hostlistingid'], $pid);
  update_field('hostname', $standard_fields['hostname'], $pid);
  update_field('listingid', $standard_fields['listingid'], $pid);
  update_field('location', $standard_fields['location'], $pid);
  update_field('map_coordinates', $standard_fields['map_coordinates'], $pid);
  update_field('mediafile', $standard_fields['mediafile'], $pid);
  update_field('neverexpire', $standard_fields['neverexpire'], $pid);
  update_field('phone', $standard_fields['phone'], $pid);
  update_field('recurrence', $standard_fields['recurrence'], $pid);
  update_field('startdate', $standard_fields['startdate'], $pid);
  update_field('starttime', $standard_fields['starttime'], $pid);
  update_field('state', $standard_fields['state'], $pid);
  update_field('times', $standard_fields['times'], $pid);
  update_field('website', $standard_fields['website'], $pid);
  update_field('zip', $standard_fields['zip'], $pid);
}

function clearOldLog($logFolder) {

  $fileDeleted = false;

  if (is_dir($logFolder)) {
    $files = array_diff(scandir($logFolder), array('.', '..'));

    if (count($files) < 6) {
      return "There are less than 5 logs stored. No deletion attempted.";
    }

    $least = INF;
    foreach ($files as $file) {
      if ($file < $least) {
        $least = $file;
      }
    }
    if ($least === INF) {
      $least = 'noFile';
    }

    $fullPath = $logFolder.$least;
    if (file_exists($fullPath)) {
      $fileDeleted = unlink($fullPath);
      if ($fileDeleted) {
        return "The log with path ".$fullPath." was deleted.";
      }
      else {
        return "The log deletion failed.";
      }
    }
  }

  return "The path stored for the log directory is incorrect";
  
}

function getLogFile($log_options, $logType) {
  $log_id = date("Ymd");
  $log_folder = $log_options[$logType.'_import_folder'];
	$log_file = $log_folder.$log_id.'_'.$logType.'_cron.log';
  return $log_file;
}

function createLog($log_options, $logType = 'listings', $cronJob, $api_results_num = false) {

  $log_id = date("Ymd");
  $log  	= "Start Cron Log -- ".date("F j, Y, g:i a").PHP_EOL.
            "--------------------------------------------------".PHP_EOL;
  if ($logType == 'events') {
    $fail_message = get_option( 'sv_api_'.$logType.'_failure_message' );
    $fail_message = $fail_message ? $fail_message : "No";
    $log .= "Did Connection Fail? ".$fail_message.PHP_EOL.
            "API Return Count: ".$api_results_num.PHP_EOL.
            "--------------------------------------------------".PHP_EOL;
  }
  else {
    $log .= 'API Connect Function Return: '.$api_results_num.PHP_EOL.
            "Fail Message: ".get_option( 'sv_api_failure_message' ).PHP_EOL.
            "--------------------------------------------------".PHP_EOL;
  }

  $log_folder = $log_options[$logType.'_import_folder'];
	$log_file = $log_folder.$log_id.'_'.$logType.'_cron.log';
	$log_success = file_put_contents ($log_file, $log, FILE_APPEND);

  // TODO: if folder does not exist, create one

  if ($log_success) {
    update_option( 'sv_api_last_'.$logType.'_import_log', $log_file );
    
    $logClearedReturn = clearOldLog($log_folder);
    
    $logClearedMessage = $logClearedReturn.PHP_EOL.
                         "--------------------------------------------------".PHP_EOL;
    
    file_put_contents ($log_file, $logClearedMessage, FILE_APPEND);
  }

  return [
    $log_success,
    $log_folder,
    $log_file
  ];
}

function addLogData($log_file, $items, $startingIndex = 1) {

  $log_info = false;
  $index    = $startingIndex;

  if ( count($items) ){
    $log_info = "";
    foreach ($items as $item_status) {
      $log_info .= $index.'. '.$item_status[0] . ' -- ' . $item_status[1].PHP_EOL;
      $index++;
    }
  }

  if ($log_info) {
    file_put_contents( $log_file, $log_info, FILE_APPEND);
  }

}

function addPagedFailMessageToLog($log_file, $page) {

  $fail_log  =  "--------------------------------------------------".PHP_EOL.
								"Fail Log. Page: ".$page.PHP_EOL.
								"Fail Message: ".get_option( 'sv_api_failure_message' ).PHP_EOL.
								"--------------------------------------------------".PHP_EOL;

	file_put_contents( $log_file, $fail_log, FILE_APPEND);

}

function process_events($type = 'manual') {
  update_option( 'sv_api_last_run_events', date("F j, Y, g:i a") );
  update_option( 'sv_api_events_failure_message', false );
  update_option( 'sv_api_event_method', $type );
  update_option( 'sv_api_events_processed', 0 );
  update_option( 'sv_api_events_updated',  0 );
  update_option( 'sv_api_events_errors', 0 );
  update_option( 'sv_api_events_added', 0 );

  $events = sv_events_api_connection();
  // return $events;
  if($events == 'error'):
    exit();
  endif;

  $processed_count  = 0;
  $updated_count 	  = 0;
  $error_count   	  = 0;
  $added_count   	  = 0;

  $log_options = get_option( 'sv_api_logs' );
  [$log_success, $log_folder, $log_file] = createLog($log_options, 'events', true, count($events) );

  $existing_event_ids = existing_event_ids();
  $processed_events = array();

  foreach($events as $event):
    $processed_count++;

    $eventid = !empty( strval($event->eventid) ) ? strval($event->eventid) : '';

    // Add new event
    if( !in_array($eventid, $existing_event_ids) ):

      array_push($existing_event_ids, $eventid);
      $create_new_event_result = create_new_event($event);

      if ($create_new_event_result[0]) {
        $added_count++;

        $return_pid = $create_new_event_result[3];
        $return_message = $create_new_event_result[1];

        $processed_events[$return_pid] = [
          $event->title,
          $return_message
        ];
      }
      else {
        $error_count++;
        
        $return_message = $create_new_event_result[1];

        $processed_events[$eventid] = [
          $event->title,
          $return_message
        ];
      }

    else :
      $existant_event = get_posts( [
        'post_type' => 'events',
        'meta_key'   => 'eventid',
        'meta_value' => $eventid,
        'posts_per_page' => 1,
        'fields' => 'ids'
      ] );

      if ($existant_event[0]) {

        $update_event_result = update_event($event, $existant_event[0]);

        if ($update_event_result[0]) {
          $updated_count++;

          $return_pid = $update_event_result[3];
          $return_message = $update_event_result[1];


          $processed_events[$return_pid] = [
            $event->title,
            $return_message
          ];
        }
        else {
          $error_count++;
          $processed_events[$eventid] = [
            $event->title,
            "Failed to update event. PID: ".$existant_event[0]
          ];
        }

      }

    endif; // add/update event
  endforeach; //$events

  update_option( 'sv_api_events_processed', $processed_count );
  update_option( 'sv_api_events_updated',  $updated_count );
  update_option( 'sv_api_events_errors', $error_count );
  update_option( 'sv_api_events_added', $added_count );

  addLogData($log_file, $processed_events);
}

function process_listings ($listings, $existing_listing_ids, $existing_companies) {

  $this_pages_listings = array();

  $processed_this_page  = 0;
  $updated_this_page 	  = 0;
  $errors_this_page 	  = 0;
  $added_this_page 	    = 0;

  foreach ($listings as $listing) {

    $processed_this_page++;

    $svid 				    = $listing['LISTINGID'];
    $last_updated 		= $listing['LASTUPDATED'];
    $company         	= !empty( $listing['COMPANY'] ) ? $listing['COMPANY'] : false;
    $sort_company 		= !empty( $listing['SORTCOMPANY'] ) ? $listing['SORTCOMPANY'] : $svid . ' Company Name Missing';
    
    // error_log(print_r($svid, true));
    // error_log(print_r($company, true));
    
    if ($company) {
      
      $type_name = $listing['TYPENAME'];
      // error_log(print_r("type name: ".$type_name, true));
      // error_log(print_r("type id: ".$listing['TYPEID'], true));
      // error_log(print_r("rank id: ".$listing['RANKID'], true));
      // error_log(print_r("rank name: ".$listing['RANKNAME'], true));
      
      // Add new listings (only add type of website)
      if( !in_array($svid, $existing_listing_ids) 
        && !in_array($company, $existing_companies) && ($type_name == "Website") ){

        array_push($existing_listing_ids, $svid);
        array_push($existing_companies, $company);

        $SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $svid);

        $create_new_listing_result = create_new_listing($SV_API_RESPONSE);

        $return_status = $create_new_listing_result[0];
        $return_message = $create_new_listing_result[1];
        $return_pid = $create_new_listing_result[3];

        if ($return_status) {
          $added_this_page++;

          $this_pages_listings[$return_pid] = [
            $sort_company,
            $return_message
          ];
        }
        else {
          $errors_this_page++;
          $this_pages_listings[$svid] = [
            $sort_company,
            "Failed to create listing."
          ];
        }
      }
      // Listing ID exists, check last_updated & update if needed
      elseif( in_array($svid, $existing_listing_ids) ) {

        $post_data = get_posts( [
          'post_type' => 'listings',
          'meta_key'   => 'listing_id',
          'meta_value' => $svid,
          'posts_per_page' => 1,
          'fields' => 'ids'
        ] );

        if ( isset( $post_data[0] ) ) { //post has been found
          $the_pid = $post_data[0];
          $svid = intval(get_field("listing_id", $the_pid));

          $SV_API_RESPONSE = sv_api_connection('getListing', 0, 0, $svid);
          $update_listing_result = update_listing($SV_API_RESPONSE, $the_pid);

          $return_status = $update_listing_result[0];
          $return_message = $update_listing_result[1];
          $return_pid = $update_listing_result[3];

          if ($return_status) {
            $updated_this_page++;
            $this_pages_listings[$return_pid] = [
              $sort_company,
              $return_message
            ];
          }
          else {
            $errors_this_page++;
            $this_pages_listings[$svid] = [
              $sort_company,
              "Failed to update listing. PID: ".$return_pid." Error: ".$return_message
            ];
          }

        }
      }

    }
  }

  return [
    $processed_this_page,
    $updated_this_page,
    $errors_this_page,
    $added_this_page,
    $this_pages_listings
  ];
}