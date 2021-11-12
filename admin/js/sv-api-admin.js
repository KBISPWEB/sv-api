(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	let page = null;
	let isTriggered = null;
	let hasMore = false;

	$(function() {
	 	$("#submit.run_now").click(function(e){
			e.preventDefault()
			$(this).prop('disabled', true)
			$('.events-table').hide()
			$('.run_import_status').addClass('content').html('<span class="import_msg">Running Listings Import. Please <strong>DO NOT</strong> leave this screen. Page will reload when finished</span>... <span class="percentStatus">0%</span>')

			page = 0
			isTriggered = true
			hasMore = false

			run_import()
		});

		if( $('.run_import_status').length ){
			// The node to be monitored
			let target = document.querySelector('.run_import_status')

			// Create an observer instance
			let observer = new MutationObserver(function(mutations) {
			  // console.log($('mydiv').text());
				if(hasMore === true) {
					run_import()
				}
			});

			let config = { attributes: true, childList: true, characterData: true }

			// Pass in the target node, as well as the observer options
			observer.observe(target, config)
		}

		$("#submit_events.run_now_events").click(function(e){
			e.preventDefault()
			$(this).prop('disabled', true)
			$('.events-table').hide()
			$('.run_import_event_status').addClass('content').html('<span class="events_import_msg">Running Events Import. Please <strong>DO NOT</strong> leave this screen. Page will reload when finished</span>...')

			isTriggered = true

			run_events_import()
		});

		$("#submit_coupons.run_now_coupons").click(function(e){
			e.preventDefault()
			$(this).prop('disabled', true)
			isTriggered = true
			run_coupons_import()
		});

		$("input#listing_id").keyup(handleChange);
		$("input#listing_id").bind("paste", handleChange);

		$("#submit_single_listing.run_now_single_listing").click(function(e){
			e.preventDefault()

			$(this).prop('disabled', true)

			let listing_id = $("input#listing_id").val()
			$("input#listing_id").val(null)

			let idType = $("input[name='id_type']:checked").val();

			isTriggered = true
			run_single_listing_import(listing_id, idType)
		});


		$(".do_not_create_new_post").click(function() {
			$("#create_new_listing_prompt").addClass("hidden");
		})

		$(".yes_create_new_post").click(function(){
			$("#create_new_listing_prompt").addClass("hidden");
			create_new_post_from_svid( parseInt($(this).data("svidToFetch")) )
		})

		$("#kill_cron").click(function(e){
			e.preventDefault()
			kill_cron();
		})
	});

	function handleChange (e) {

		var submit_button = $("#submit_single_listing.run_now_single_listing")
		var alert_text = $("#single-listings-alert-text")
		var status_text = $('.run_import_single_listing_status')

		if ( $(this).val().length > 2 ) {
			submit_button.prop('disabled', false)
			alert_text.addClass("hidden")
			status_text.html("")
		}
		else {
			submit_button.prop('disabled', true)
			alert_text.removeClass("hidden")
		}

	}

	function reloadpage(){
		location.reload();
	}

	function run_import() {
		let str = {
			'action': 'run_import',
			'page': page,
			'is_triggered': isTriggered
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			success: function(data){
				console.log(data)
				hasMore = data.hasMore
				page = data.page

				$('.percentStatus').html(data.percent+'%')
				$('.run_import_status').append(data.logData)
				if(hasMore == false) {
					$('.run_import_status').append('<br />Listings Import Completed! Please wait 10 seconds while the page reloads....')
					setTimeout( reloadpage, 10000 )
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$('.run_import_status').append('<br>Import failed! See errors below.')
				$('.run_import_status').append(JSON.stringify(jqXHR) + " :: " + textStatus + " :: " + errorThrown)
			},
		});
	}

	function run_events_import(){
		let str = {
			'action': 'run_events_import',
			'is_triggered': isTriggered
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			success: function(data){
				$('.run_import_event_status').append('<br />Events Import Completed! Please wait 10 seconds while the page reloads....')
				setTimeout( reloadpage, 10000 )
			},
			error: function(jqXHR, textStatus, errorThrown) {
			},
		});
	}

	function run_coupons_import(){
		let str = {
			'action': 'run_coupons_import',
			'is_triggered': isTriggered
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			// add a loader
			success: function(data){
				$('#ajaxLoader').html("");
				$('.run_import_coupons_status').append('<br />Coupons import completed!')
				$("#submit_coupons.run_now_coupons").prop('disabled', false)
			},
			beforeSend : function(){
		    var svg = "<svg class='mx-auto ajax-window' width='80px'  height='80px'  xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' preserveAspectRatio='xMidYMid' class='lds-wedges'><g transform='translate(50,50)'><g transform='scale(0.7)'><g transform='translate(-50,-50)'><g transform='rotate(239.504 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='0.75s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c1}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' fill-opacity='0.8' fill='#005A55'></path></g><g transform='rotate(359.628 50.0024 50.0024)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c2}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(90 50 50)' fill-opacity='0.8' fill='#00C7CD'></path></g><g transform='rotate(119.752 50 50)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1.5s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c3}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(180 50 50)' fill-opacity='0.8' fill='#ED592A'></path></g><g transform='rotate(239.876 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='3s' begin='0s' repeatCount='indefinite'></animateTransform> <path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c4}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(270 50 50)' fill-opacity='0.8' fill='#F9B7B6'></path></g></g></g></g></svg>";
		    var svg_markup = "<div class='w-full text-center' style='height:60px'>"
		                      +svg+
		                    "</div>";
		    $('#ajaxLoader').html(svg_markup);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			},
		});
	}

	function run_single_listing_import(pid, idType="sv") {
		let str = {
			'action': 'run_single_listing_import',
			'is_triggered': isTriggered,
			'pid': pid,
			'idType': idType
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			// add a loader
			success: function(data){
				console.log("DATA");
				console.log(data);

				if (data.postFound === true) { // post exists
					if (data.status === true) { // success
						$('.run_import_single_listing_status').append(
							'<br /><div style="margin-left:5px;" class="success-text">Listing Updated. See Listing <a href='
							+ data.link
							+ '>Here</a></div>'
						)
					}
					else { // failure
						$('.run_import_single_listing_status').append(
							'<br /><div style="margin-left:5px;" class="alert-text">Listing Update Failed. See Listing <a href='
							+ data.link
							+ '>Here</a></div>'
						)
					}
				}
				else {
					if (data.createNew === true) {
						$("#create_new_listing_prompt").removeClass("hidden");
						$("#sv_id_display").html(data.svid)
						$(".yes_create_new_post").data("svidToFetch", data.svid)
					}
					else {
						$('.run_import_single_listing_status').append(
							'<br /><div style="margin-left:5px;" class="alert-text">There is no WordPress post with that ID.</div>'
						)
					}
				}
				$('#single_listing_ajaxLoader').html("");
				$("#submit_single_listing").prop('disabled', true)
			},
			beforeSend : function(){
		    var svg = "<svg class='mx-auto ajax-window' width='80px'  height='80px'  xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' preserveAspectRatio='xMidYMid' class='lds-wedges'><g transform='translate(50,50)'><g transform='scale(0.7)'><g transform='translate(-50,-50)'><g transform='rotate(239.504 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='0.75s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c1}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' fill-opacity='0.8' fill='#005A55'></path></g><g transform='rotate(359.628 50.0024 50.0024)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c2}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(90 50 50)' fill-opacity='0.8' fill='#00C7CD'></path></g><g transform='rotate(119.752 50 50)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1.5s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c3}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(180 50 50)' fill-opacity='0.8' fill='#ED592A'></path></g><g transform='rotate(239.876 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='3s' begin='0s' repeatCount='indefinite'></animateTransform> <path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c4}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(270 50 50)' fill-opacity='0.8' fill='#F9B7B6'></path></g></g></g></g></svg>";
		    var svg_markup = "<div class='w-full text-center' style='height:60px'>"
		                      +svg+
		                    "</div>";
		    $('#single_listing_ajaxLoader').html(svg_markup);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(errorThrown)
				$('#single_listing_ajaxLoader').html("");
				$('.run_import_single_listing_status').append(
						'<br /><div style="margin-left:5px;" class="alert-text">Listing Update Failed.</div>'
				)
				$("#submit_single_listing").prop('disabled', true)
			},
		});
	}

	function create_new_post_from_svid(svid) {
		let str = {
			'action': 'create_new_post_from_svid',
			'svid': svid
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			// add a loader
			success: function(data){
				console.log("DATA");
				console.log(data);
				if (data.status === true) {
					$('.run_import_single_listing_status').append(
						'<br /><div style="margin-left:5px;" class="success-text">See new listing <a href='
							+ data.link
							+ '>Here</a></div>'
					)
				}
				else {
					$('.run_import_single_listing_status').append(
						'<br /><div style="margin-left:5px;" class="alert-text">'+data.returnMessage+'</div>'
					)
				}
				$('#single_listing_ajaxLoader').html("");
				$("#submit_single_listing").prop('disabled', true)
			},
			beforeSend : function(){
		    var svg = "<svg class='mx-auto ajax-window' width='80px'  height='80px'  xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' preserveAspectRatio='xMidYMid' class='lds-wedges'><g transform='translate(50,50)'><g transform='scale(0.7)'><g transform='translate(-50,-50)'><g transform='rotate(239.504 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='0.75s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c1}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' fill-opacity='0.8' fill='#005A55'></path></g><g transform='rotate(359.628 50.0024 50.0024)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c2}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(90 50 50)' fill-opacity='0.8' fill='#00C7CD'></path></g><g transform='rotate(119.752 50 50)'> <animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='1.5s' begin='0s' repeatCount='indefinite'></animateTransform><path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c3}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(180 50 50)' fill-opacity='0.8' fill='#ED592A'></path></g><g transform='rotate(239.876 50 50)'><animateTransform attributeName='transform' type='rotate' calcMode='linear' values='0 50 50;360 50 50' keyTimes='0;1' dur='3s' begin='0s' repeatCount='indefinite'></animateTransform> <path ng-attr-fill-opacity='{{config.opacity}}' ng-attr-fill='{{config.c4}}' d='M50 50L50 0A50 50 0 0 1 100 50Z' transform='rotate(270 50 50)' fill-opacity='0.8' fill='#F9B7B6'></path></g></g></g></g></svg>";
		    var svg_markup = "<div class='w-full text-center' style='height:60px'>"
		                      +svg+
		                    "</div>";
		    $('#single_listing_ajaxLoader').html(svg_markup);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(errorThrown)
				$('#single_listing_ajaxLoader').html("");
				$('.run_import_single_listing_status').append(
						'<br /><div style="margin-left:5px;" class="alert-text">Failed To Create Listing</div>'
				)
				$("#submit_single_listing").prop('disabled', true)
			},
		});
	}

	function kill_cron() {
		let str = {
			'action': 'kill_cron'
		};

		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajax_object.ajax_url,
			data: str,
			success: function(data){
				$('.kill_cron_status').html('<div style="margin-top:10px;" class="success-text">Cron has been killed.</div>')
			},
			error: function(jqXHR, textStatus, errorThrown) {
				$('.kill_cron_status').html('<div style="margin-top:10px;" class="alert-text">Attempt to kill cron failed.</div>')
			},
		});
	}

})( jQuery );
