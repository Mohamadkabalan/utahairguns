<?php
/*
Plugin Name: Request Form for Zendesk
Plugin URI: https://wordpress.org/plugins/zendesk-request-form/
Version: 3.0.3
Author: pipdig
Description: Add a Zendesk request form on any post/page via a shortcode. Submitted messages will create a new ticket in your Zendesk account.
Author URI: https://www.pipdig.co/
Text Domain: zendesk-request-form
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*/

if (!defined('ABSPATH')) die;

// Load languages
function zendesk_request_form_textdomain() {
	load_plugin_textdomain('zendesk-request-form', false, 'zendesk-request-form/languages');
}
add_action('plugins_loaded', 'zendesk_request_form_textdomain');


// create global for allowed html tags
global $zrf_allowedtags;
$zrf_allowedtags = array(
	'a' => array(
		'href' => true,
		'title' => true,
		'target' => true,
		'rel' => true,
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'div' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'b' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'p' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'em' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'h2' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'h3' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'h4' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'i' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'img' => array(
		'class' => true,
		'id' => true,
		'style' => true,
		'src' => true,
		'title' => true,
		'alt' => true,
	),
	'br' => array(),
	'strong' => array(
		'class' => true,
		'id' => true,
		'style' => true,
	),
	'style' => array(
		'scoped' => true,
	),
);

// form action
include(plugin_dir_path(__FILE__).'inc/action.php');

// admin settings
include(plugin_dir_path(__FILE__).'inc/settings.php');

// custom fields
include(plugin_dir_path(__FILE__).'inc/custom-fields.php');

// Try to clean up mistakes from people entering incorrect urls
function zrf_clean_url($url) {
	$url = rtrim($url, '/');
	$url = str_replace('http://http://', 'http://', $url);
    if ((strlen($url) > 5)) {
		if (strpos($url, 'https://') === false) {
			if (strpos($url, 'http://') === false) {
				$url = 'http://'.$url;
			}
		}
    }
    return $url;
}

// Make sure jQuery is enqueued
function zrf_enqueue_scripts() {
	wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'zrf_enqueue_scripts');

// Shortcode [zendesk_request_form]
function zendesk_request_form_shortcode($atts, $content = null) {
	
	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	
	extract(shortcode_atts(array(
		'subject' => '', // pre-fill or remove subject (set to "no" or "off" to disable)
		'priority' => '', // set ticket priority or provide list of options for user to select from
		'description' => '', // pre-fill or remove description (set to "no" or "off" to disable)
		'size' => '8', // textarea size for description (if description enabled)
		'users' => '', // whether to grab WP user data or not
		'name' => '', // pre-fill name https://wordpress.org/support/topic/prepopulate-email-field
		'email' => '', // pre-fill email https://wordpress.org/support/topic/prepopulate-email-field
		'subject_prefix' => '', // prefix subject when sent to Zendesk
		'subject_suffix' => '', // suffix subject when sent to Zendesk
		'group' => '', // custom field group - defines the fields displayed in this form
		'custom_fields_position' => 'before', // position of the custom fields within the form ("before" or "after" the main message field)
		'attachments' => '', // Allow te user to upload a file with the ticket
		'attachments_allowed' => '.jpg,.jpeg,.png,.gif,.pdf,.txt,.csv,.xls,.xlsx,.doc,.docx', // limit uploads to specific file types
		'attachments_max_size' => '1', // Max size for file attachments. Can be 1, 7 or 20 depending on your Zendesk plan level.
		'attachments_required' => '', // Force the user to attach a file.
		'redirect' => '', // url to redirect after successful submission
		'new_window' => '', // open "success" page in a new browser tab/window.
		'cf_geo' => '', // custom field ID (from Zendesk) for CF geo ip.
		'assignee' => '', // default assignee for ticket. Should be the user ID of desired agent
		'useragent' => 'yes', // add User Agent data to ticket. (On by default. Set to "no" or "off" to disable)
		'cc' => '', // send ticket to an email address in addition to Zendesk
		'split_name' => '', // split the "Name field" into "First Name" and "Last Name"
		'show_captcha' => 'yes', // url to redirect after successful submission
		'label_name' => esc_html__('Your Name', 'zendesk-request-form'),
		'label_email' => esc_html__('Your Email', 'zendesk-request-form'),
		'label_subject' => esc_html__('Subject', 'zendesk-request-form'),
		'label_description' => esc_html__('Your Message', 'zendesk-request-form'),
		'label_submit' => esc_html__('Submit', 'zendesk-request-form'),
		'label_priority' => esc_html__('Priority of your message', 'zendesk-request-form'),
		'label_attachments' => esc_html__('Upload a file', 'zendesk-request-form'),
		'label_max_size' => esc_html__('Maximum size:', 'zendesk-request-form'),
		'label_file_types' => esc_html__('Allowed file types:', 'zendesk-request-form'),
	), $atts));
	
	$output = '';
	
	$options = get_option('zendesk_request_form_settings');
	
	$ticket_num = 0;
	if (isset($_GET['zrf_ticket']) && isset($_GET['zx'])) {
		$ticket_num = intval($_GET['zrf_ticket']);
	}
	
	if ($ticket_num != 0) {
		
		$message = esc_html__('Your message has been sent successfully.', 'zendesk-request-form');
		if (!empty($options['zendesk_message'])) {
			$message = esc_html($options['zendesk_message']);
		}
		$output .= '<p id="zrf-confirm">'.$message.'</p>';
		$output .= '<p>'.sprintf(esc_html__('Your ticket number is %s.', 'zendesk-request-form'), $ticket_num).'</p>';
		
	} else {
		
		global $zrf_allowedtags;
		
		if (empty($redirect)) { // if redirect url is not set via shortcode option, let's auto direct back to form page
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
				$redirect = 'https://';
			} else {
				$redirect = 'http://';
			}
			$redirect .= strip_tags($_SERVER['SERVER_NAME']);
			if ($_SERVER['SERVER_PORT'] != 80) {
				$redirect .= ':'.strip_tags($_SERVER["SERVER_PORT"]).strip_tags($_SERVER["REQUEST_URI"]);
			} else {
				$redirect .= strip_tags($_SERVER["REQUEST_URI"]);
			}
		}
		

		// grab data from user if logged in
		$current_user = $requester_email = $first_name = $last_name = $requester_name = $user_id = '';
		if (is_user_logged_in() && ($users != 'no')) {
			$current_user = wp_get_current_user();
			$requester_email = sanitize_email($current_user->user_email);
			//$url = esc_url($current_user->user_url);
			$first_name = sanitize_text_field($current_user->user_firstname);
			$last_name = sanitize_text_field($current_user->user_lastname);
			if ($first_name && $last_name) {
				$requester_name = $first_name.' '.$last_name;
			}
		} else { // if not logged in, let's check for "email" query in url
			if (isset($_GET["email"])) {
				$requester_email = sanitize_email($_GET["email"]);
			}
		}
		
		// Create the output for custom fields.
		// used later in the output, evaluating now so we can set enctype in <form> if file attachment required.
		$custom_fields_output = $enctype = $att_required = '';
		
		if ($attachments == 'yes' || $attachments == 'true' || $attachments == 'on') {
			$attachments = 1;
		}
		
		if (absint($attachments)) {
			$enctype = 'enctype="multipart/form-data"';
			$max_size = absint($attachments_max_size);
			if ($max_size === 7) {
				$max_size_mb = '7340032';
			} elseif ($max_size === 20) {
				$max_size_mb = '20971520';
			} else {
				$max_size_mb = '1048576';
			}
			
			$attachments_required = filter_var($attachments_required, FILTER_VALIDATE_BOOLEAN);
			if ($attachments_required) {
				$att_required = 'required';
			}
			
		}
		
		if ($group) {
		
			$custom_fields_output .= '<input type="hidden" name="zen_field_group" value="'.esc_attr($group).'" readonly>';
		
			$custom_fields = new WP_Query(
				array(
					'post_type' => 'zrf_custom_field',
					'showposts' => -1,
					'orderby' => 'menu_order',
					'zrf_field_group' => $group
				)
			);
			while ($custom_fields->have_posts()): $custom_fields->the_post();
				
				$id = get_the_ID();
				$field_type = esc_attr(get_post_meta($id, 'field-type', true));
				
				if ($field_type != 'descriptive') { // check if it's actually a field or just description text/html
				
					$default_value = $extra_info = '';
					
					$field_title = sanitize_text_field(get_post_meta($id, 'field-title', true));
					$field_id = sanitize_text_field(get_post_meta($id, 'field-id', true));
					$field_placeholder = sanitize_text_field(get_post_meta($id, 'field-placeholder', true));
					$field_default = esc_attr(get_post_meta($id, 'field-default', true));
					$field_required = sanitize_text_field(get_post_meta($id, 'field-required', true));
					$field_pattern = sanitize_text_field(get_post_meta($id, 'field-pattern', true));
					$field_get = sanitize_text_field(get_post_meta($id, 'field-get', true));
					$extra_info = wp_kses(get_post_meta($id, 'field-extra', true), $zrf_allowedtags);
						 
					if (empty($field_title) && ($field_type != 'hidden')) {
						return 'Error: There is a custom field without a "Title / Label"';
					}
					if (empty($field_id)) {
						return 'Error: There is a custom field without an "ID"';
					}
					if (empty($field_placeholder)) {
						$field_placeholder = '';
					}
					if (empty($field_type)) {
						$field_type = 'text';
					}
					if (empty($field_required)) {
						$field_required = '';
					} else {
						$field_required = 'required="required"';
					}
					if (!empty($field_pattern)) {
						$field_pattern = ' pattern="'.$field_pattern.'"';
					}
					if (!empty($field_default)) {
						$default_value = $field_default;
					}
					if (!empty($extra_info)) {
						$extra_info = ' <span class="zrf_extra_info">'.wp_kses(get_post_meta($id, 'field-extra', true), $zrf_allowedtags).'</span>';
					}
					if (!empty($field_get)) { // or we retrieve value from a GET query
						if (isset($_GET[$field_get])) {
							$default_value = sanitize_text_field($_GET[$field_get]);
						}
					}
					
					$custom_fields_output .= '<p id="zendesk_field_'.$field_id.'">';
					
						if ($field_type == 'checkbox') { // checkbox needs it's own styling
						
							$custom_fields_output .= '<input type="'.$field_type.'" name="'.$field_id.'" id="'.$field_id.'" '.$field_required.'>';
							$custom_fields_output .= '  <span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>'.$extra_info;
							
						} elseif ($field_type == 'textarea') {
							
							$custom_fields_output .= '<span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>';
							$custom_fields_output .= '<textarea name="'.$field_id.'" id="'.$field_id.'" '.$field_required.'>'.$default_value.'</textarea>';
							
						} elseif ($field_type == 'dropdown') { // dropdown needs it's own markup
						
							$field_options = strip_tags(get_post_meta($id, 'field-options', true));
							
							$custom_fields_output .= '<span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>'.$extra_info;
							$custom_fields_output .= '<select name="'.$field_id.'" id="'.$field_id.'" '.$field_required.'>';
							$custom_fields_output .= '<option value="">'.esc_html__('Select an option', 'zendesk-request-form').'</option>';
							$field_options_arr = explode("\r\n", $field_options);
							if (!empty($field_options_arr[0])) {
								$field_selected_unset = true;
								foreach ($field_options_arr as $field_option_line) {
									// avoid blank lines in user entry
									if (strlen($field_option_line) < 3) {
										continue;
									}
									$field_option_line_item = explode("|", $field_option_line);
									if (!empty($field_option_line_item[0]) && !empty($field_option_line_item[1])) {
										//$custom_fields_output .= $field_option_line_item[0]."<br />";
										// set this option as default?
										$field_selected = '';
										if ($field_selected_unset && !empty($field_option_line_item[2]) && strtolower(trim($field_option_line_item[2])) == 'default') {
											$field_selected = 'selected';
											$field_selected_unset = false;
										}
										$custom_fields_output .= '<option value="'.esc_attr($field_option_line_item[0]).'" '.$field_selected.'>'.strip_tags($field_option_line_item[1]).'</option>';
									} else {
										$custom_fields_output .= '<option value="">Invalid options set for this field. Please check settings.</option>';
									}
								}
							} else {
								$custom_fields_output .= '<option value="">Invalid options set for this field. Please check settings.</option>';
							}
							
							$custom_fields_output .= '</select>';
							
						} elseif ($field_type == 'date') {
							
							wp_enqueue_script('jquery-ui-datepicker');
							$custom_fields_output .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/redmond/jquery-ui.min.css" />';
							
							$custom_fields_output .= '<span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>'.$extra_info;
							$custom_fields_output .= '  <input type="text" id="'.$field_id.'" '.$field_required.'>';
							$custom_fields_output .= '  <input type="text" name="'.$field_id.'" id="alt_'.$field_id.'" style="display:none">';
							$date_format = sanitize_text_field(get_post_meta($id, 'field-date-format', true));
							if (empty($date_format)) {
								$date_format = 'yy-mm-dd';
							}
							$custom_fields_output .= '
							<script>
							jQuery(document).ready(function($) {
								$("#'.$field_id.'").datepicker({ dateFormat: "'.$date_format.'", altField: "#alt_'.$field_id.'", altFormat: "yy-mm-dd" });
							});
							</script>';
						
						} elseif ($field_type == 'url') {
							
							$custom_fields_output .= '<span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>'.$extra_info;
							$custom_fields_output .= '  <input class="zrf_input_url" autocomplete="off" autocorrect="off" autocapitalize="off" type="url" name="'.$field_id.'" id="'.$field_id.'" placeholder="'.$field_placeholder.'" value="'.$default_value.'" '.$field_pattern.' '.$field_required.'>';
							
						} else {
							
							if ($field_type != 'hidden') {
								$custom_fields_output .= '<span class="zrf_field_title"><label for="'.$field_id.'">'.$field_title.'</label></span>'.$extra_info;
							}
							$custom_fields_output .= '  <input autocomplete="off" autocorrect="off" autocapitalize="off" type="'.$field_type.'" name="'.$field_id.'" id="'.$field_id.'" placeholder="'.$field_placeholder.'" value="'.$default_value.'" '.$field_pattern.' '.$field_required.'>';
							
						}
					
					$custom_fields_output .= '</p>';
					
				} else { // it's not actually a field, so let's display the html/text from the description
					global $zrf_allowedtags;
					$custom_fields_output .= wp_kses(get_post_meta($id, 'field-description', true), $zrf_allowedtags);
				}
				
			endwhile;
			
			wp_reset_query();
		
		}
		
		$target = '';
		$new_window = filter_var($new_window, FILTER_VALIDATE_BOOLEAN);
		if ($new_window) {
			$target = 'target="_blank"';
		}
		
		$output .= '<style> .zrf-form label, .zrf-form input[type=text], .zrf-form input[type=password], .zrf-form input[type=tel], .zrf-form input[type=email], .zrf-form input[type=file], .zrf-form textarea, .zrf-form select {width:100%;font-family:inherit;font-size:inherit;} .zrf_extra_info {font-size: 80%;} </style>';
		$output .= '<div id="zrf-form" class="zrf-form">';
		$output .= '<form method="post" action="'.esc_url(admin_url('admin-ajax.php')).'" '.$target.' '.$enctype.'>';
		
		$show_captcha = filter_var($show_captcha, FILTER_VALIDATE_BOOLEAN);
		
		if ($show_captcha && !is_user_logged_in() && !empty($options['google_captcha_public']) && !empty($options['google_captcha_secret'])) {
			$output .= '<scr'.'ipt src="https://www.google.com/recaptcha/api.js?render='.esc_attr($options['google_captcha_public']).'"></scr'.'ipt>';
			$output .= '<scr'.'ipt>';
				$output .= 'grecaptcha.ready(function () {';
					$output .= 'grecaptcha.execute("'.esc_attr($options['google_captcha_public']).'", {action: "contact"}).then(function (token) {';
						$output .= 'var recaptchaResponse = document.getElementById("recaptchaResponse");';
						$output .= 'recaptchaResponse.value = token;';
					$output .= '});';
				$output .= '});';
			$output .= '</scr'.'ipt>';
			$output .= '<input type="hidden" name="recaptcha_response" id="recaptchaResponse" autocomplete="off">';
		}
		
		//$output .= wp_nonce_field('zrf_nonce_action', 'zrf_nonce_field', false, false);
		$output .= '<input type="hidden" name="zen_begin" value="zen_begin" readonly>';
		$output .= '<input type="hidden" name="zen_return_url" value="'.esc_url($redirect).'">';
		
		if (!is_user_logged_in()) { // add anti-spam fields only for not logged in users
			$output .= '<p style="display:none"><input type="text" name="zrf-email-website" value="" autocomplete="off"></p>';
		}
		
		// name
		$split_name = filter_var($split_name, FILTER_VALIDATE_BOOLEAN);
		if ($split_name) {
			$output .= '<p id="zendesk_field_zen_name_1"><label for="zen_name_1"><span class="zrf_field_title">'.esc_html__('First name', 'zendesk-request-form').'</span></label>';
			if (strlen($first_name) > 1) { // taken from user account
				$output .= '  <input type="text" name="zen_name_1" id="zen_name_1" class="zen_name_field" value="'.$first_name.'" required>';
			} else { // standard name field
				$output .= '  <input type="text" name="zen_name_1" id="zen_name_1" class="zen_name_field" value="" required>';
			}
			$output .= '</p>';
			$output .= '<p id="zendesk_field_zen_name_2"><label for="zen_name_2"><span class="zrf_field_title">'.esc_html__('Last name', 'zendesk-request-form').'</span></label>';
			if (strlen($last_name) > 1) { // taken from user account
				$output .= '  <input type="text" name="zen_name_2" id="zen_name_2" class="zen_name_field" value="'.$last_name.'" required>';
			} else { // standard name field
				$output .= '  <input type="text" name="zen_name_2" id="zen_name_2" class="zen_name_field" value="" required>';
			}
			$output .= '</p>';
		} else {
			$output .= '<p id="zendesk_field_zen_name"><label for="zen_name"><span class="zrf_field_title">'.strip_tags($label_name).'</span></label>';
			if (strlen($requester_name) > 1) { // taken from user account
				$output .= '  <input type="text" name="zen_name" id="zen_name" class="zen_name_field" value="'.$requester_name.'" required>';
			} elseif ($name) { // taken from shortcode pre-fill
				$output .= '  <input type="text" name="zen_name" id="zen_name" class="zen_name_field" value="'.esc_attr($name).'" required>';
			} else { // standard name field
				$output .= '  <input type="text" name="zen_name" id="zen_name" class="zen_name_field" value="" required>';
			}
			$output .= '</p>';
		}
		
		
		// email
		$output .= '<p id="zendesk_field_zen_email"><label for="zen_email"><span class="zrf_field_title">'.strip_tags($label_email).'</span></label>';
		if (strlen($requester_email) > 3) { // taken from user account
			$output .= '  <input type="email" name="zen_email" id="zen_email" value="'.$requester_email.'" required>';
		} elseif ($email) { // taken from shortcode pre-fill
			$output .= '  <input type="email" name="zen_email" id="zen_email" value="'.esc_attr($email).'" required>';
		} else { // standard name field
			$output .= '  <input type="email" name="zen_email" id="zen_email" value="" required>';
		}
		$output .= '</p>';
		
		
		if ($priority) {
			
			$priority_options_arr = explode(",", $priority);
			if (count($priority_options_arr) > 1) {
				// multiple priorities set in shortcode list, so lets display them as options for user
				$output .= '<p id="zendesk_field_priority"><label for="zen_priority"><span class="zrf_field_title">'.$label_priority.'</span></label>';
				$output .= '<select name="zen_priority" id="zen_priority" required>';
					$output .= '<option value="">'.esc_html__('Select an option', 'zendesk-request-form').'</option>';
					foreach ($priority_options_arr as $priority_option) {
						$priority_option = sanitize_text_field(strtolower ($priority_option));
						$output .= '<option value="'.$priority_option.'">'.ucfirst($priority_option).'</option>';
					}
				$output .= '</select>';
				
				$output .= '</p>';
			} else {
				// only one priority set in shortode, so lets auto-set ticket to this.
				$output .= '<input type="hidden" name="zen_priority" id="zen_priority" value="'.esc_attr($priority).'" readonly>';
			}
			
		}
		
		// add the custom fields output from earlier
		if ($custom_fields_position == 'before' && $custom_fields_output) {
			$output .= $custom_fields_output;
		}
		
		if ($subject == 'no') { // no subject required
			$output .= '<input type="hidden" name="zen_subject" value="" readonly>';
		} elseif ($subject != '') { // subject pre-filled with shortcode attribute
			$output .= '<input type="hidden" name="zen_subject" value="'.esc_attr($subject).'" readonly>';
		} else { // standard subject field
			$output .= '<p id="zendesk_field_zen_subject"><label for="zen_subject"><span class="zrf_field_title">'.strip_tags($label_subject).'</span></label>';
			$output .= '  <input type="text" name="zen_subject" id="zen_subject" value="" required>';
			$output .= '</p>';
		}
		
		// prefix and suffix send to action
		if ($subject_prefix) {
			$output .= '<input type="hidden" name="zen_subject_prefix" value="'.esc_attr($subject_prefix).'" readonly>';
		}
		if ($subject_suffix) {
			$output .= '<input type="hidden" name="zen_subject_suffix" value="'.esc_attr($subject_suffix).'" readonly>';
		}
		
		if ($assignee) {
			$output .= '<input type="hidden" name="zen_assignee" value="'.sanitize_text_field($assignee).'" readonly>';
		}
		
		if (!empty($cf_geo)) {
			$output .= '<input type="hidden" name="zen_cf_geo" value="'.sanitize_text_field($cf_geo).'" readonly>';
		}
		
		if ($description == 'no') { // no description required
			$output .= '<input type="hidden" name="zen_desc" value="'.esc_html__('No description set in ticket', 'zendesk-request-form').'" readonly>';
		} elseif ($description == 'optional') { // description pre-filled with shortcode attribute
			$output .= '<p id="zendesk_field_zen_desc"><label for="zen_desc"><span class="zrf_field_title">'.strip_tags($label_description).'</span></label>';
			$output .= '  <textarea name="zen_desc" id="zen_desc" rows="'.absint($size).'"></textarea>';
			$output .= '</p>';
		} elseif ($description != '') { // description pre-filled with shortcode attribute
			$output .= '<input type="hidden" name="zen_desc" value="'.esc_attr($description).'" readonly>';
		} else { // standard description field
			$output .= '<p id="zendesk_field_zen_desc"><label for="zen_desc"><span class="zrf_field_title">'.strip_tags($label_description).'</span></label>';
			$output .= '  <textarea name="zen_desc" id="zen_desc" rows="'.absint($size).'" required></textarea>';
			$output .= '</p>';
		}
		
		if (absint($attachments)) {
			
			$attachments_allowed = str_replace(' ', '', $attachments_allowed);
			$attachments_allowed_print = str_replace(',', ', ', $attachments_allowed);
			
			$multiple_attachments = '';
			if (absint($attachments) > 1) {
				$multiple_attachments = 'multiple';
			}
			
			$output .= '<p id="zendesk_field_zen_files"><label for="zen_files"><span class="zrf_field_title">'.strip_tags($label_attachments).'</span></label>';
			$output .= '  <input type="file" id="zen_files" name="zen_files[]" accept="'.sanitize_text_field($attachments_allowed).','.sanitize_text_field(strtoupper($attachments_allowed)).'" '.$multiple_attachments.' '.$att_required.'>';
			$output .= '  <span class="zrf_extra_info" id="zrf_accepted_filetypes">'.$label_max_size.' '.$max_size.'MB.<br />'.$label_file_types.' '.sanitize_text_field($attachments_allowed_print).'.</span>';
			$output .= '</p>';
		}
		
		// add the custom fields output from earlier
		if ($custom_fields_position == 'after' && $custom_fields_output) {
			$output .= $custom_fields_output;
		}
		
		if (is_email($cc)) {
			$mask_email = str_replace('@', '__zrf_at____', $cc);
			$output .= '<input type="hidden" name="zen_cc" value="'.esc_attr($mask_email).'">';
		}
		
		// send user-agent (enabled by default)
		$useragent = filter_var($useragent, FILTER_VALIDATE_BOOLEAN);
		if ($useragent) {
			$output .= '<input type="hidden" name="zen_user_agent" value="1">';
		}
		
		// GA tracking
		$google_analytics_tracking = 'onClick=\'ga("send", "event", { eventCategory: "Zendesk Request Form", eventAction: "Form Submitted" });\'';
		if ($group) {
			// get form group name for GA tracking
			$group_data = get_term_by('slug', $group, 'zrf_field_group');
			$group_name = $group_data->name;
			$google_analytics_tracking = 'onClick=\'ga("send", "event", { eventCategory: "Zendesk Request Form", eventAction: "Form Submitted", eventLabel: "'.esc_attr($group_name).'" });\'';
		}
		
		$output .= '<input type="hidden" name="action" value="zrf_form_action">'; // form action
		
		$output .= '<p id="zendesk_field_zen_submit">';
		$button_type = (bool) apply_filters('zrf_submit_as_button', false); // https://wordpress.org/support/topic/submit-button-66/
		if ($button_type === true) {
			$output .= '  <button type="submit" id="zrf_submit" value="'.esc_attr($label_submit).'" '.$google_analytics_tracking.' >'.esc_html($label_submit).'</button>';
		} else {
			$output .= '  <input type="submit" id="zrf_submit" value="'.esc_attr($label_submit).'" '.$google_analytics_tracking.' >';
		}
		$output .= '</p>';
		$output .= '</form>';
		$output .= '</div>';
		
		$accepted_files = explode(",", $attachments_allowed);
		$attachment_validation_js = '';
		
		if (absint($attachments) && is_array($accepted_files)) {
			
			// https://www.sanwebe.com/2013/10/check-input-file-size-before-submit-file-api-jquery
			$attachment_validation_js = '
			if (window.File && window.FileReader && window.FileList && window.Blob) {
				
				var fileUpload = $("input[type=\'file\']");
				if (parseInt(fileUpload.get(0).files.length) > '.absint($attachments).'){
					e.preventDefault();
					alert("'.sprintf(esc_html__('You can only upload a maximum of %s files.', 'zendesk-request-form'), absint($attachments)).'");
				}
				
				var fileInput = $("#zen_files");
				
				//get data from file input field
				if (fileInput.val()) {
					var fileSize = fileInput[0].files[0].size;
					var fileName = fileInput[0].files[0].name;
				
					// check if file is correct size
					if (fileSize > '.$max_size_mb.') {
						e.preventDefault();
						alert("'.esc_attr(__('Uploaded file is too large.', 'zendesk-request-form')).'");
					}
					
					// check if file type is allowed				
					var re = /(?:\.([^.]+))?$/; // https://stackoverflow.com/questions/680929/how-to-extract-extension-from-filename-string-in-javascript
					var fileExt = "." + re.exec(fileName)[1];
					var validExts = '.json_encode($accepted_files).';
					
					if ($.inArray(fileExt, validExts) == -1) {
						e.preventDefault();
						alert("'.esc_attr(__('Uploaded file type is not allowed.', 'zendesk-request-form')).'");
					}
				}
			}
			';
		}
		
		$output .= '
			<script>
			
			jQuery(document).ready(function($) {
				
				// first letter of name uppercase
				$(".zen_name_field").on("keydown", function(event) {
					if (this.selectionStart == 0 && event.keyCode >= 65 && event.keyCode <= 90 && !(event.shiftKey) && !(event.ctrlKey) && !(event.metaKey) && !(event.altKey)) {
						var $t = $(this);
						event.preventDefault();
						var char = String.fromCharCode(event.keyCode);
						$t.val(char + $t.val().slice(this.selectionEnd));
						this.setSelectionRange(1,1);
					}
				});
				
				// double click protection to stop duplicate submissions
				$("#zrf_submit").removeAttr("disabled");
				$("#zrf-form").bind("submit", function(e) {
					'.$attachment_validation_js.'
					
					// check any URL fields
					$(this).find(".zrf_input_url").each(function() {
						// is the field required and has been left as http:// default?
						if (($(this).prop("required"))&& ($(this).val() == "http://")) {
							alert("'.esc_attr(__('Please enter a valid URL', 'zendesk-request-form')).'");
							$(this).css("border", "2px solid #000");
							e.preventDefault();
							return false;
						}
					});
					
					// disable submit button
					$(this).find("#zrf_submit").attr("disabled", "disabled");
					// re-enable after 3 seconds
					setTimeout(function() {
						$("form").find("#zrf_submit").removeAttr("disabled");
					}, 3000);
					
				});
				
			});
			</script>';
	}
	
	return $output;
}
add_shortcode('zendesk_request_form', 'zendesk_request_form_shortcode');



// [zrf_ticket_num]
// shortcode to retreive ticket number on custom redirect page
function zendesk_request_retrieve_ticket_num_shortcode($atts, $content = null) {
	$output = '';
	if (isset($_GET['zrf_ticket']) && isset($_GET['zx'])) {
		$output = sanitize_text_field($_GET["zrf_ticket"]);
	}
	return $output;
}
add_shortcode('zrf_ticket_num', 'zendesk_request_retrieve_ticket_num_shortcode');




// Add settings link to plugins page
function zendesk_request_plugin_action_links($links) {
	$links[] = '<a href="'.get_admin_url(null, 'options-general.php?page=zendesk-request-form').'">Settings</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'zendesk_request_plugin_action_links');



function zrf_upload_attachment($path, $filename, $token = '') {
	
	$zendesk_settings = get_option('zendesk_request_form_settings');

	if (empty($zendesk_settings['zendesk_token'])) {
		return false;
	} else {
		$zendesk_token = sanitize_text_field($zendesk_settings['zendesk_token']);
	}
	if (empty($zendesk_settings['zendesk_user'])) {
		return false;
	} else {
		$zendesk_user = sanitize_email($zendesk_settings['zendesk_user']);
	}
	if (empty($zendesk_settings['zendesk_domain'])) {
		return false;
	} else {
		$zendesk_subdomain = sanitize_text_field($zendesk_settings['zendesk_domain']);
	}
	
	// https://gerhardpotgieter.com/2014/07/30/uploading-files-with-wp_remote_post-or-wp_remote_request/
	$file = @fopen($path, 'r');
	$file_size = filesize($path);
	$file_data = fread($file, $file_size);
	
	// https://codex.wordpress.org/Function_Reference/wp_check_filetype
	$filetype = wp_check_filetype($filename);
	$mime = $filetype['type'];
	$ext = $filetype['ext'];
	
	//var_dump($filetype);
	//die;

	$args = array(
		'method' => 'POST',
		'headers' => array(
			'authorization' => 'Basic '.base64_encode($zendesk_user.'/token:'.$zendesk_token),
			'accept' => 'application/json',
			'content-type' => $mime,
		),
		'timeout' => 15,
		'body' => $file_data
	);
	
	$add_token = '';
	if ($token) {
		$add_token = '&token='.$token;
	}
	
	$result = wp_remote_request('https://'.$zendesk_subdomain.'.zendesk.com/api/v2/uploads.json?filename='.$filename.$add_token, $args);

	if (!is_wp_error($result)) {
		$response = json_decode($result['body']);
		if (!empty($response->upload->token)) {
			return trim(strip_tags($response->upload->token));
			die;
		}
	}

}



// convert CF country code to name
function zrf_code_to_country($code){

	$code = strtoupper($code);

	$countryList = array(
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BQ' => 'Bonaire, Saint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'VG' => 'British Virgin Islands',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CW' => 'Curacao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CD' => 'Democratic Republic of the Congo',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TL' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'CI' => 'Ivory Coast',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'XK' => 'Kosovo',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'CG' => 'Republic of the Congo',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SX' => 'Sint Maarten',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'KR' => 'South Korea',
		'SS' => 'South Sudan',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'VI' => 'U.S. Virgin Islands',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VA' => 'Vatican',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);

	if(!$countryList[$code]) return $code;
	else return $countryList[$code];
}
