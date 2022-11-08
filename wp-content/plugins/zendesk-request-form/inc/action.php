<?php
if (!defined('ABSPATH')) die;

function zrf_form_action() {
	
	$zendesk_settings = get_option('zendesk_request_form_settings');
	
	$error_msg = esc_html__('Please try again by clicking the back button on your browser.', 'zendesk-request-form');
	
	if (!empty($zendesk_settings['google_captcha_secret']) && !empty($_POST["recaptcha_response"])) {
		
		$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify?secret='.sanitize_text_field($zendesk_settings['google_captcha_secret']).'&response='.sanitize_text_field($_POST["recaptcha_response"]);
		$recaptcha_response = wp_remote_retrieve_body(wp_remote_post($recaptcha_url));
		if ($recaptcha_response) {
			$recaptcha = json_decode($recaptcha_response);
			if (isset($recaptcha->score) && $recaptcha->score < 0.4) {
				wp_die('Error: Spam check failed. '.$error_msg);
			}
		}
		
	}
	
	
	if (current_user_can('manage_options')) {
		$error_msg_settings = 'Please check the API settings under "Settings > Zendesk Form". This message is only visible to Admins.';
	} else {
		$error_msg_settings = esc_html__('Request not submitted. Please contact the Admin of this website so they can check the settings for this form.', 'zendesk-request-form');
	}
	
	
	
	$failure_redirect = '';
	if (!empty($zendesk_settings['zendesk_fail_redirect'])) {
		$failure_redirect = $zendesk_settings['zendesk_fail_redirect'];
	}
	
	$failure_email_to = '';
	if (!empty($zendesk_settings['zendesk_failure_email'])) {
		$failure_email_to = $zendesk_settings['zendesk_failure_email'];
	}

	if (empty($zendesk_settings['zendesk_token'])) {
		wp_die('Error: '.$error_msg_settings);
	} else {
		$zendesk_token = sanitize_text_field($zendesk_settings['zendesk_token']);
	}
	if (empty($zendesk_settings['zendesk_user'])) {
		wp_die('Error: '.$error_msg_settings);
	} else {
		$zendesk_user = sanitize_email($zendesk_settings['zendesk_user']);
	}
	if (empty($zendesk_settings['zendesk_domain'])) {
		wp_die('Error: '.$error_msg_settings);
	} else {
		$zendesk_url = esc_url_raw('https://'.trim($zendesk_settings['zendesk_domain']).'.zendesk.com/api/v2/tickets.json');
	}

	/*
	if (!isset($_POST['zrf_nonce_field']) || !wp_verify_nonce($_POST['zrf_nonce_field'],'zrf_nonce_action')) {
		if ($failure_redirect) {
			wp_redirect(wp_sanitize_redirect($failure_redirect));
			die;
		}
		$admin_nonce_msg = '';
		if (is_super_admin()) {
			$admin_nonce_msg = ' Admin notice: "nonce" check failed on form.';
		}
		wp_die('Error: '.$error_msg.$admin_nonce_msg);
	}
	*/
	
	// Check if fake field has any content. If so, it's spam
	if (!empty($_POST["zrf-email-website"])) {
		wp_die('Error: '.esc_html__('Spam check failed.', 'zendesk-request-form').' '.$error_msg);
	}

	// Location, for returning after submission
	if (isset($_POST["zen_return_url"])) {
		$return_url = esc_url_raw(trim($_POST["zen_return_url"]));
		if (empty($return_url)) {
			wp_die('Error: Invalid return url. '.$error_msg);
		}
	} else {
		wp_die('Error: Invalid return url. '.$error_msg);
	}

	// Name field
	if (isset($_POST["zen_name"])) {
		$name = sanitize_text_field($_POST["zen_name"]);
		/*
		$name = ucwords(strtolower($name));
		$name = implode('-', array_map('ucfirst', explode('-', $name)));
		*/
		/*
		if (function_exists('mb_substr') && function_exists('mb_convert_case')) {
			if (mb_substr($name, 0, 1) == mb_strtoupper(mb_substr($name, 0, 1))) {
				// https://stackoverflow.com/questions/39389721/how-to-check-first-character-in-string-is-capital
				// first letter is already uppercase
			} else {
				$name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8"); //http://php.net/manual/en/function.ucwords.php#112079
			}
		}
		*/
		if (empty($name)) {
			wp_die('Error: '.esc_html__('No name entered.', 'zendesk-request-form').' '.$error_msg);
		}
	} elseif (isset($_POST["zen_name_1"]) && isset($_POST["zen_name_2"])) {
		$first_name = sanitize_text_field($_POST["zen_name_1"]);
		$first_name = ucwords(strtolower($first_name));
		$last_name = sanitize_text_field($_POST["zen_name_2"]);
		/*
		if (function_exists('mb_substr') && function_exists('mb_convert_case')) {
			if (mb_substr($last_name, 0, 1) == mb_strtoupper(mb_substr($last_name, 0, 1))) {
				// https://stackoverflow.com/questions/39389721/how-to-check-first-character-in-string-is-capital
				// first letter is already uppercase
			} else {
				$last_name = mb_convert_case($last_name, MB_CASE_TITLE, "UTF-8"); //http://php.net/manual/en/function.ucwords.php#112079
			}
		}
		*/
		$name = $first_name.' '.$last_name;
	} else {
		wp_die('Error: '.esc_html__('No name entered.', 'zendesk-request-form').' '.$error_msg);
	}

	// Email field
	if (isset($_POST["zen_email"])) {
		$email = sanitize_email($_POST["zen_email"]);
		if (empty($email)) {
			wp_die('Error: '.esc_html__('No email address entered.', 'zendesk-request-form').' '.$error_msg);
		}
		//$email = str_replace('googlemail.com', 'gmail.com', $email);
	} else {
		wp_die('Error: '.esc_html__('No email address entered.', 'zendesk-request-form').' '.$error_msg);
	}

	// subject
	$subject = '';
	if (isset($_POST["zen_subject"])) {
		$subject = sanitize_text_field($_POST["zen_subject"]);
		if (empty($subject)) {
			$subject = '';
		}
	}

	// Subject prefix, passed from shortcode
	$subject_prefix = '';
	if (isset($_POST["zen_subject_prefix"])) {
		$subject_prefix = esc_attr($_POST["zen_subject_prefix"]);
	}

	// Subject suffix, set via shortcode but not public yet
	$subject_suffix = '';
	if (isset($_POST["zen_subject_suffix"])) {
		$subject_suffix = esc_attr($_POST["zen_subject_suffix"]);
		if ($subject_suffix == 'name') {
			$subject_suffix = ': '.$name;
		}
	}


	// description field
	$desc = '-';
	if (isset($_POST["zen_desc"]) && !empty($_POST["zen_desc"])) {
		$desc = strip_tags($_POST["zen_desc"]);
	}
	
	$token = array();
	
	//var_dump($_FILES["zen_files"]);
	//die;
	$i = 0;
	
	while ($_FILES['zen_files']['tmp_name'][$i]){

		if (!empty($_FILES["zen_files"]["tmp_name"][$i])) {
			$path = $_FILES["zen_files"]["tmp_name"][$i];
			if (filesize($path) > 20971520) {
				wp_die('Error: '.esc_html__('Uploaded file is too large.', 'zendesk-request-form').' '.$error_msg);
			}
			$filename = $_FILES['zen_files']['name'][$i];
			//var_dump($filename);
			//die;
			$get_token = zrf_upload_attachment($path, $filename, $get_token = '');
			if ($get_token) {
				$token[] = $get_token;
			}
		}	
		$i++;

		if ($i == count($_FILES['zen_files']['tmp_name'])){
			break;
		}
	}
	
	//var_dump($token);
	//die;
	
	// assignee
	$assignee = '';
	if (isset($_POST["zen_assignee"])) {
		$assignee = sanitize_text_field($_POST["zen_assignee"]);
		if (empty($assignee)) {
			$assignee = '';
		}
	}
	
	// priority
	$priority = '';
	if (isset($_POST["zen_priority"])) {
		$priority = sanitize_text_field($_POST["zen_priority"]);
		if (empty($priority)) {
			$priority = '';
		}
	}

	// custom field group
	$field_group = '';
	if (isset($_POST["zen_field_group"])) {
		$field_group = sanitize_text_field($_POST["zen_field_group"]);
		if (empty($subject)) {
			$field_group = '';
		}
	}


	$desc_suffix = ''; // desc suffix denoted by custom field property
	
	$custom_fields = new WP_Query(
		array(
			'post_type' => 'zrf_custom_field',
			'showposts' => -1,
			'zrf_field_group' => $field_group,
		)
	);
	$custom_fields_array = array();
	while ($custom_fields->have_posts()): $custom_fields->the_post();
		$id = get_the_ID();
		$field_type = esc_attr(get_post_meta($id, 'field-type', true));		
		if ($field_type != 'descriptive') { // check if it's actually a field or just description text/html
			$field_id = sanitize_text_field(get_post_meta($id, 'field-id', true));
			if (isset($_POST[$field_id])) {
				
				// add prefix or suffix?
				$data_prefix = $data_suffix = '';
				$data_prefix = strip_tags(get_post_meta($id, 'field-prefix', true)); // strip_tags rather than sanitize_text_field so spaces are retained
				$data_suffix = strip_tags(get_post_meta($id, 'field-suffix', true));
				
				$custom_field_data = '';
				if ($field_type == 'url') { // check if url so we can sanitize properly
					if ($_POST[$field_id] != 'http://') {
						$custom_field_data = $data_prefix . zrf_clean_url($_POST[$field_id]) . $data_suffix;
					}
				} else {
					$custom_field_data = $data_prefix . sanitize_text_field($_POST[$field_id]) . $data_suffix;
				}
				
				if ($custom_field_data) {
					$custom_fields_array += array($field_id => $custom_field_data);
				
					// add this field to description content?
					$add_to_desc_checked = sanitize_text_field(get_post_meta($id, 'field-desc-suffix', true));
					if ($add_to_desc_checked) {
						$field_title = sanitize_text_field(get_post_meta($id, 'field-title', true));
						//$desc_suffix .= "\n\n".$custom_field_data;
						$desc_suffix .= "\n\n".rtrim($field_title, ':').": ".$custom_field_data;
					}
				}
				
			}
		}
	endwhile;
	
	wp_reset_query();

	// country code from cloudflare
	$country = '';
	if (!empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
		$country_code = sanitize_text_field(strtoupper($_SERVER["HTTP_CF_IPCOUNTRY"]));
		$country = zrf_code_to_country($country_code);
	}

	// add country code to custom field if defined via shortcode
	if ($country && (isset($_POST["zen_cf_geo"]))) {
		$cf_geo_field_id = sanitize_text_field($_POST["zen_cf_geo"]);
		$custom_fields_array += array($cf_geo_field_id => $country);	
	}

	// user-agent
	if (isset($_POST["zen_user_agent"])) {
		$user_agent = "\n\n".sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
		// add cloudflare country to user-agent
		if ($country) {
			$user_agent .= ' '.$country;
		}
	} else {
		$user_agent = '';
	}
	
	//print_r ($custom_fields_array); die;

	$body = array(
		'ticket' => array(
			'requester' => array(
				'name' => stripslashes($name),
				'email' => stripslashes($email)
			),
			'priority' => $priority,
			'assignee_id' => $assignee,
			'custom_fields' => apply_filters('zrf_custom_fields_data', $custom_fields_array),
			'subject' => stripslashes($subject_prefix.$subject.$subject_suffix),
			'comment' => array (
				'body' => stripslashes($desc.$desc_suffix.$user_agent),
				'uploads' => $token,
			),
		)
	);
	
	$body = apply_filters('zrf_message_body', $body);
	
	$headers = array(
		'authorization' => 'Basic '.base64_encode($zendesk_user.'/token:'.$zendesk_token),
		'content-type' => 'application/json'
	);

	$args = array(
		'method' => 'POST',
		'timeout' => 10,
		'headers' => $headers,
		'body' => json_encode($body)
	);

	$response = wp_remote_request($zendesk_url, $args);

	//print_r($response);

	if (is_wp_error($response)) {
		if ($failure_email_to) {
			$failed_body = "Hi there!\n\rSomeone just tried to send a message via a form, but the connection to Zendesk failed :(\n\r Subject:".$body['ticket']['subject']."\n\rContent: \r\n".$body['ticket']['comment']['body']."\n\r\n\r";
			$errors = $response->get_error_messages();
			if (is_array($errors)) {
				$failed_body .= "WP_Error Message: ".implode("\n\r",$errors);
			}
			wp_mail($failure_email_to, 'Zendesk Form Failed', $failed_body);
		}
		if ($failure_redirect) {
			wp_redirect(wp_sanitize_redirect($failure_redirect));
			die;
		}
		wp_die('Error: '.esc_html__('Ticket not created.', 'zendesk-request-form').' The response from the server was "WP_Error". Please click the "Back" button on your browser and try again.');
	}

	$resp_code = intval($response['response']['code']);

	if ($resp_code != 201) { // 201 = ticket is "created" response
		if (is_super_admin()) {
			if (isset($response['body'])) {
				echo 'Error submitting form. Only site admins can see this error data:<br /><br />';
				echo '<pre>';
				print_r(json_decode($response['body'], true));
				echo '</pre>';
				wp_die();
			} else {
				wp_die('Error: '.$error_msg);
			}
		} else {
			if ($failure_redirect) {
				wp_redirect(wp_sanitize_redirect($failure_redirect));
				die;
			}
			wp_die('Error: '.$error_msg);
		}
	}

	$ticket_num = 0;
	$body_resp = json_decode($response['body'], true);
	
	//wp_die($response);

	if (!empty($body_resp['ticket']['id'])) {
		$ticket_num = sanitize_text_field($body_resp['ticket']['id']);	
	} else {
		wp_die(esc_html__('Your message has been received. We will get back to you as soon as possible.', 'zendesk-request-form'));
	}

	if (!$ticket_num) {
		wp_die(esc_html__('Your message has been received. We will get back to you as soon as possible.', 'zendesk-request-form'));
	}
	
	// Do what you like with the form data. For example, can be used to send custom form data via wp_mail https://gist.github.com/pipdig/9a6104862342c4d32f92cd41a6005b6a
	do_action('zrf_form_submitted', $body);
	
	// if cc option is set, send form to email
	if (isset($_POST["zen_cc"])) {
		$cc = strip_tags($_POST["zen_cc"]);
		$unmask_email = str_replace('__zrf_at____', '@', $cc);
		if (is_email($unmask_email)) {
			$subject = $body['ticket']['subject'];
			$message = $body['ticket']['comment']['body'];
			wp_mail($unmask_email, $subject, $message);
		}
	}
	
	// check if return url has query param already
	if(strpos($return_url,'?') !== false) {
		$return_url .= '&zrf_ticket='.$ticket_num.'&zx=1#zrf-confirm';
	} else {
		$return_url .= '?zrf_ticket='.$ticket_num.'&zx=1#zrf-confirm';
	}

	wp_redirect(wp_sanitize_redirect($return_url));
	die;
}
add_action('wp_ajax_nopriv_zrf_form_action', 'zrf_form_action');
add_action('wp_ajax_zrf_form_action', 'zrf_form_action');