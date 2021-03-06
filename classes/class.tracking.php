<?php

class CTA_Conversion_Tracking {

	/**
	*  Initializes Class
	*/
	public function __construct() {

		self::load_hooks();

	}

	public static function load_hooks() {

		/* track masked cta links */
		add_action( 'inbound_track_link' , array( __CLASS__ , 'track_link' ) );

		/* Track form submissions related to call to actions a conversions */
		add_action('inboundnow_store_lead_pre_filter_data' , array( __CLASS__ , 'set_form_submission_conversion' ) , 20 , 1 );
	}

	/**
	*  Listens for tracked masked link processing
	*/
	public static function track_link( $args ) {

		$do_not_track = apply_filters('inbound_analytics_stop_track' , false );

		if ( $do_not_track ) {
			return;
		}

		self::store_click_data( $args['cta_id'] , $args['vid'] );
		if (isset($args['id'])) {

			self::store_as_cta_click(  $args['cta_id'] , $args['vid'] , $args['id'] , 'clicked-link' );
			self::store_as_conversion(  $args['cta_id'] , $args['id'] , $args['vid'] );
		}

	}

	/**
	*  Listens for tracked form submissions embedded in calls to actions & incrememnt conversions
	*/
	public static function set_form_submission_conversion( $data ) {

		parse_str($data['raw_params'] , $raw_post_values );

		if (!isset($raw_post_values['wp_cta_id']) || !$raw_post_values['wp_cta_id'] ) {
			return $data;
		}

		$do_not_track = apply_filters('inbound_analytics_stop_track' , false );

		if ( $do_not_track ) {
			return;
		}

		$cta_id = $raw_post_values['wp_cta_id'];
		$vid = $raw_post_values['wp_cta_vid'];

		$lp_conversions = get_post_meta( $cta_id , 'wp-cta-ab-variation-conversions-'.$vid, true );
		$lp_conversions++;
		update_post_meta(  $cta_id , 'wp-cta-ab-variation-conversions-'.$vid, $lp_conversions );

		return $data;
	}

	/**
	 * Store the click data to the correct CTA variation
	 *
	 * @param  INT $cta_id      cta id
	 * @param  INT $lead_id       lead id
	 * @param  INT $variation_id which variation was clicked
	 */
	public static function store_click_data($cta_id, $variation_id) {
		// If leads_triggered meta exists do this
		$event_trigger_log = get_post_meta( $cta_id , 'leads_triggered' ,true );
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);
		$conversion_count = get_post_meta($cta_id,'wp-cta-ab-variation-conversions-'.$variation_id ,true);
		$conversion_count++;
		update_post_meta($cta_id, 'wp-cta-ab-variation-conversions-'.$variation_id, $conversion_count);
		update_post_meta($cta_id, 'wp_cta_last_triggered', $wordpress_date_time ); // update last fired date
	}

	/**
	*  	Store click event to lead profile
	*
	*  @param INT $cta_id
	*/
	public static function store_as_cta_click($cta_id, $vid, $lead_id, $event_type) {
		$timezone_format = 'Y-m-d G:i:s T';
		$wordpress_date_time =  date_i18n($timezone_format);

		if ( $lead_id ) {
			$event_data = get_post_meta( $lead_id, 'call_to_action_clicks', TRUE );
			$event_count = get_post_meta( $lead_id, 'wp_cta_trigger_count', TRUE );
			$event_count++;

			$individual_event_count = get_post_meta( $lead_id, 'lt_event_tracked_'.$cta_id, TRUE );
			$individual_event_count = ($individual_event_count != "") ? $individual_event_count : 0;
			$individual_event_count++;

			if ($event_data) {
				$event_data = json_decode($event_data,true);
				$event_data[$event_count]['id'] = $cta_id;
				$event_data[$event_count]['datetime'] = $wordpress_date_time;
				$event_data[$event_count]['type'] = $event_type;
				$event_data[$event_count]['variation'] = $vid;
				$event_data = json_encode($event_data);
				update_post_meta( $lead_id, 'call_to_action_clicks', $event_data );
				update_post_meta( $lead_id, 'wp_cta_trigger_count', $event_count );
				//	update_post_meta( $lead_id, 'lt_event_tracked_'.$cta_id, $individual_event_count );
			} else {
				$event_data[1]['id'] = $cta_id;
				$event_data[1]['datetime'] = $wordpress_date_time;
				$event_data[1]['type'] = $event_type;
				$event_data[$event_count]['variation'] = $vid;
				$event_data = json_encode($event_data);
				update_post_meta( $lead_id, 'call_to_action_clicks', $event_data );
				update_post_meta( $lead_id, 'wp_cta_trigger_count', 1 );
				//	update_post_meta( $lead_id, 'lt_event_tracked_'.$cta_id, $individual_event_count );
			}
		}
	}

	/**
	 * Stores lead as conversion
	 * @param $cta_id
	 * @param $lead_id
	 * @param $variation_id
	 */
	public static function store_as_conversion($cta_id, $lead_id, $variation_id ) {

		$time = current_time( 'timestamp', 0 ); // Current wordpress time from settings
		$wordpress_date_time = date("Y-m-d G:i:s T", $time);

		if ( !$lead_id ) {
			return;
		}

		$conversion_data = get_post_meta( $lead_id, 'wpleads_conversion_data', TRUE );


		if (!$conversion_data) {
			$conversion_data = array();
		} else {
			$conversion_data = json_decode($conversion_data,true);
		}

		$conversion_data[]['id'] = $cta_id;
		$conversion_data[]['variation'] = $variation_id;
		$conversion_data[]['datetime'] = $wordpress_date_time;
		$conversion_data = json_encode($conversion_data);


		update_post_meta( $lead_id, 'wpleads_conversion_data', $conversion_data );
		update_post_meta( $lead_id, 'wpl-lead-conversion-count', count($conversion_data) );

	}
}

$CTA_Conversion_Tracking = new CTA_Conversion_Tracking();