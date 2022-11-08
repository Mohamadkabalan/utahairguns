<?php
if (!defined('ABSPATH')) die;

// display admin notice if no API set
function zendesk_request_form_admin_notice() {
	$options = get_option('zendesk_request_form_settings');
	if (!current_user_can('manage_options') || !empty($options['zendesk_token'])) {
		return;
	}
	global $pagenow;
	if ($pagenow != 'plugins.php') {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<h2>Howdy!</h2>
		<p>The <strong>Zendesk Request Form</strong> plugin is active. Please setup your API key on <a href="<?php echo admin_url('options-general.php?page=zendesk-request-form'); ?>">this page</a> to get started.</p>
		<br />
	</div>
	<?php
}
add_action('admin_notices', 'zendesk_request_form_admin_notice');


// admin settings
function zendesk_request_form_add_admin_menu() { 
	add_options_page('Zendesk Request Form', 'Zendesk Form', 'manage_options', 'zendesk-request-form', 'zendesk_request_form_options_page');
}
add_action('admin_menu', 'zendesk_request_form_add_admin_menu');

function zendesk_request_form_settings_init() { 

	register_setting('zrf_settings_page', 'zendesk_request_form_settings');
	
	add_settings_section(
		'zrf_settings_page_section',
		'', //section description
		'zendesk_request_form_settings_section_callback',
		'zrf_settings_page'
	);

	add_settings_field(
		'zendesk_token',
		'Zendesk API Token (<a href="https://support.zendesk.com/hc/en-us/articles/226022787-Generating-a-new-API-token-" target="_blank" rel="noopener">?</a>)',
		'zendesk_token_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);

	add_settings_field(
		'zendesk_user',
		'Zendesk admin email',
		'zendesk_user_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);

	add_settings_field(
		'zendesk_domain',
		'Zendesk subdomain',
		'zendesk_domain_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);
	
	add_settings_field(
		'zendesk_message',
		'Success message',
		'zendesk_message_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);
	
	add_settings_field(
		'zendesk_fail_redirect',
		'Redirect on fail',
		'zendesk_fail_redirect_render',
		'zrf_settings_page',
		'zrf_settings_page_section' 
	);
	
	add_settings_field(
		'zendesk_failure_email',
		'Email on fail',
		'zendesk_failure_email_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);
	
	add_settings_field(
		'google_captcha_public',
		'Google reCAPTCHA Public Key',
		'zrf_google_captcha_public_key_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);
	
	add_settings_field(
		'google_captcha_secret',
		'Google reCAPTCHA Private Key',
		'zrf_google_captcha_secret_key_render',
		'zrf_settings_page',
		'zrf_settings_page_section'
	);

}
add_action('admin_init', 'zendesk_request_form_settings_init');


function zendesk_token_render() { 
	$options = get_option('zendesk_request_form_settings');
	$token = '';
	if (isset($options['zendesk_token'])) {
		$token = sanitize_text_field($options['zendesk_token']);
	}
	?>
	<input type="password" id="zrf_zendesk_token" name="zendesk_request_form_settings[zendesk_token]" value="<?php echo $token; ?>" class="zrf_wider_field">
	<?php
}


function zendesk_user_render() { 
	$options = get_option('zendesk_request_form_settings');
	$user = '';
	if (isset($options['zendesk_user'])) {
		$user = sanitize_email($options['zendesk_user']);
	}
	?>
	<input type="email" id="zrf_zendesk_user" name="zendesk_request_form_settings[zendesk_user]" value="<?php echo $user; ?>" placeholder="john@example.com" class="zrf_wider_field">
	<?php
}


function zendesk_domain_render() { 
	$options = get_option('zendesk_request_form_settings');
	$domain = '';
	if (isset($options['zendesk_domain'])) {
		$domain = sanitize_text_field($options['zendesk_domain']);
	}
	?>
	https://<input type="text" id="zrf_zendesk_domain" name="zendesk_request_form_settings[zendesk_domain]" value="<?php echo $domain; ?>" placeholder="example" style="width: 210px">.zendesk.com
	<?php
}


function zendesk_message_render() { 
	$options = get_option('zendesk_request_form_settings');
	$message = '';
	if (isset($options['zendesk_message'])) {
		$message = sanitize_text_field($options['zendesk_message']);
	}
	?>
	<input type="text" name="zendesk_request_form_settings[zendesk_message]" value="<?php echo $message; ?>" placeholder="<?php _e('Your message has been sent successfully.', 'zendesk-request-form'); ?>" class="zrf_wider_field zrf_hidden_optional">
	<br />
	<span style="font-size:80%">Only displayed on forms which do not have the "redirect" <a href="#shortcode-attributes">shortcode attribute</a>.</span>
	<?php
}

function zendesk_fail_redirect_render() { 
	$options = get_option('zendesk_request_form_settings');
	$fail_redirect = '';
	if (isset($options['zendesk_fail_redirect'])) {
		$fail_redirect = esc_url($options['zendesk_fail_redirect']);
	}
	?>
	<input type="url" name="zendesk_request_form_settings[zendesk_fail_redirect]" value="<?php echo $fail_redirect; ?>" placeholder="https://example.com/message-failed" class="zrf_wider_field zrf_hidden_optional">
	<br />
	<span style="font-size:80%">The user will be redirected to this URL if the form fails for any reason. For example, you could create a page with instructions to contact you if the form fails or the Zendesk API goes offline. Only non-admin users will be redirected. Admins will see a debug message instead.</span>
	<?php
}

function zendesk_failure_email_render() { 
	$options = get_option('zendesk_request_form_settings');
	$zendesk_failure_email = '';
	if (isset($options['zendesk_failure_email'])) {
		$zendesk_failure_email = sanitize_email($options['zendesk_failure_email']);
	}
	?>
	<input type="email" name="zendesk_request_form_settings[zendesk_failure_email]" value="<?php echo $zendesk_failure_email; ?>" placeholder="john@example.com" class="zrf_wider_field zrf_hidden_optional">
	<br />
	<span style="font-size:80%">If the Zendesk API is offline when the form is submitted, the message will be sent to this email address instead.</span>
	<?php
}

function zrf_google_captcha_public_key_render() { 
	$options = get_option('zendesk_request_form_settings');
	$key = '';
	if (isset($options['google_captcha_public'])) {
		$key = sanitize_text_field($options['google_captcha_public']);
	}
	?>
	<input type="text" id="google_captcha_public" name="zendesk_request_form_settings[google_captcha_public]" value="<?php echo $key; ?>" class="zrf_wider_field zrf_hidden_optional">
	<br />
	<span style="font-size:80%">See notes below.</span>
	<?php
}

function zrf_google_captcha_secret_key_render() { 
	$options = get_option('zendesk_request_form_settings');
	$key = '';
	if (isset($options['google_captcha_secret'])) {
		$key = sanitize_text_field($options['google_captcha_secret']);
	}
	?>
	<input type="text" id="google_captcha_secret" name="zendesk_request_form_settings[google_captcha_secret]" value="<?php echo $key; ?>" class="zrf_wider_field zrf_hidden_optional">
	<br />
	<span style="font-size:80%">You can get your Public and Private Key from <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">this page</a>. Make sure to select "reCAPTCHA v3" when creating your account. Please note that the reCAPTCHA form is not always shown, depending on whether Google thinks you are a robot.</span>
	<?php
}


function zendesk_request_form_settings_section_callback() {
	?>
	<h2><span class="dashicons dashicons-admin-plugins"></span> Main Settings</h2>
	<p>After completing these settings, use the shortcode [zendesk_request_form] to add the form to any post/page.</p>
	<?php
}

function zendesk_request_form_options_page() { 

	if (!current_user_can('manage_options')) {
		wp_die();
	}
	
	?>
	<div class="wrap">
	
	<form action="options.php" method="post" autocomplete="off">
	
		<style>
		.card {
			max-width: 720px;
		}
		.zrf_infotab_reveal {
			display: none;
			background: #f4f4f4;
			padding: 8px;
			border-radius: 6px;
		}
		.zrf_infotab_icon {
			cursor: pointer;
		}
		.piperror {
			color: red;
		}
		.pipsuccess {
			color: green;
		}
		.zrf_wider_field {
			width: 100%;
		}
		</style>
		
		<h1>Zendesk Request Form</h1>
		
		<div class="card">
		<?php
			settings_fields('zrf_settings_page');
			do_settings_sections('zrf_settings_page');
			echo '<p><a href="#" class="button" id="showExtraZrfOptions">Show Advanced Options</a></p>';
			submit_button();
		?>
		
		<hr>
		
		<h2>Current Connection Status</h2>
		<p id="zrf_test_connection_result">After saving the settings, this area will show your connection status to the Zendesk API.</p>
		
		<script>
		jQuery(document).ready(function($) {
			
			$('.zrf_hidden_optional').closest('tr').hide();
			
			$('#showExtraZrfOptions').click(function(e) {
				e.preventDefault();
				$('.zrf_hidden_optional').closest('tr').fadeIn();
				$(this).hide();
			});
			
			var domain = $("#zrf_zendesk_domain").val();
			var user = $("#zrf_zendesk_user").val();
			var token = $("#zrf_zendesk_token").val();
			
			if ((domain.length > 1) && (user.length > 1) && (token.length > 1)) {
				var data = {
					'action': 'zrf_connection_tester',
					'sec' : <?php echo "'".wp_create_nonce('zrf_check_nonce')."'"; ?>,
					'domain': domain,
					'user': user,
					'token': token,
				};
				
				$.post(ajaxurl, data, function(response) {
					//alert(response);
					$('#zrf_test_connection_result').html(response);
				});
			}
		
		});
		</script>
		
		</div><!--// .card -->
		
	</form>
	
	<div class="card">
		<h2><span class="dashicons dashicons-testimonial"></span> Custom Fields</h2>
		<p>Here you can add any <a href="https://support.zendesk.com/hc/en-us/articles/203661496-Adding-and-using-custom-ticket-fields" target="_blank">Custom Fields</a> you have created in Zendesk.</p>
		<p>The following field types are supported: text, number, url, email, dropdown, password, hidden, descriptive (used to display information within the form).</p>
		<p><input class="button" type="button" value="Manage Custom Fields" onclick="window.location='<?php echo admin_url('edit.php?post_type=zrf_custom_field'); ?>';" /></p>
		<p><input class="button" type="button" value="Add New Field" onclick="window.location='<?php echo admin_url('post-new.php?post_type=zrf_custom_field'); ?>';" /></p>
	</div><!--// .card -->
	
	<div class="card">
		<h2><span class="dashicons dashicons-index-card"></span> Custom Field Groups</h2>
		<p>You can assign Custom Fields to a form by using Custom Field Groups.</p>
		<p>For example: [zendesk_request_form group="new-enquiry"] would create a form with all of the Custom Fields assigned to a "New Enquiry" group. This means you can create multiple forms with different fields</p>
		<p>Custom Fields will only be displayed on a form if a group is set in the <a href="#shortcode-attributes">shortcode attributes</a>.</p>
		<p><input class="button" type="button" value="Manage Field Groups" onclick="window.location='<?php echo admin_url('edit-tags.php?taxonomy=zrf_field_group&post_type=zrf_custom_field'); ?>';" /></p>
	</div><!--// .card -->
	
	
	<div id="shortcode-attributes" class="card">
		<h2><span class="dashicons dashicons-lightbulb"></span> Shortcode Generator</h2>
	
		<p>You can use the options below to generate a shortcode to display a form. Simply copy the following shortcode into a page to display the form on your site.</p>
		
		<textarea id="zrf_gen_result" class="large-text" onClick="this.select();" readonly>[zendesk_request_form]</textarea>
		
		<h2>Options:</h2>
		
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>
		
		<table class="form-table">
			<tr>
				<th scope="row"><label for="zrf_split_name">Display "Name" as two separate fields</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="checkbox" id="zrf_split_name" name="zrf_split_name" value='true'><p class="zrf_infotab_reveal">Enabling this option will split the "Your name" field into two options: "First name" and "Last name".</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_subject_on">Display subject field?</label></th>
				<td><input type="checkbox" id="zrf_subject_on" name="zrf_subject_on" value='true' checked></td>
			</tr>
			<tr class="zrf_subject_row" style="display: none">
				<th scope="row"><label for="zrf_subject">Ticket subject title</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="text" id="zrf_subject" name="zrf_subject" class="widefat"><p class="zrf_infotab_reveal">Pre-fill the subject of the message. If you set a subject here then the user will not be able to set their own. This will also hide the subject field from the form.</p></td>
			</tr>
			<tr class="zrf_subject_row" id="zrf_subject_row_prefix" style="display: none">
				<th scope="row"><label for="zrf_subject_prefix">Ticket subject prefix</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="text" id="zrf_subject_prefix" name="zrf_subject_prefix" class="widefat"><p class="zrf_infotab_reveal">Use this option to add text to the front of the subject. For example, you may wish to prefix the subject of an inquiry form with "Customer Inquiry: ". The subject will then come through to Zendesk as "Customer Inquiry: &lt;User subject here&gt;".</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_priority">Ticket priority</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td>
				<select id="zrf_priority" multiple="multiple">
					<option value="low">Low</option>
					<option value="normal">Normal</option>
					<option value="high">High</option>
					<option value="urgent">Urgent</option>
				</select>
				<p class="zrf_infotab_reveal">This option can be set as a single priority or a list of options. For example, setting this to "high" would set all tickets submitted via the form to High priority. Setting this option as "normal,high,urgent" would give the user those 3 options to choose from in the form.</p></td>
			</tr>
			<tr>
				<th scope="row">Custom Fields Group <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td>
					<?php
					$field_groups_dropdown = 'No custom field groups available. Would you like to create one?';
					$tax_terms = get_terms('zrf_field_group');
					if ($tax_terms && !is_wp_error($tax_terms)) {
						$field_groups_dropdown = '<select id="zrf_group"><option value="" selected>- None -</option>';
						foreach ($tax_terms as $tax_term) {
							$field_groups_dropdown .= '<option value="'.$tax_term->slug.'"> '.$tax_term->name.'</option>';
						}
						$field_groups_dropdown .= '</select>';
					}
					echo $field_groups_dropdown;
					?>
					<p class="zrf_infotab_reveal">When creating a Custom Field, you can add it to a Custom Field Group. You can then add a Custom Field Group to a form. This will allow the form to show all the Custom Fields in that group.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_attachments">Allow file attachments?</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="checkbox" id="zrf_attachments" name="zrf_attachments" value='true'><p class="zrf_infotab_reveal">Enabling this option will allow users to upload a file with their ticket. You can also restrict the file types and size using the options below.</p></td>
			</tr>
			<tr class="zrf_attachments_row" style="display: none">
				<th scope="row"><label for="zrf_attachments_size">Maximum attachment size</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td>
					<select id="zrf_attachments_size">
						<option value="1" selected>1 MB</option>
						<option value="7">7 MB</option>
						<option value="20">20 MB</option>
					</select>
					<p class="zrf_infotab_reveal">The maximum file size a user can upload to the ticket. Defaults to 1mb if not set. See <a href="https://support.zendesk.com/hc/en-us/articles/235860287" target="_blank">this page</a> for further information on size limits in Zendesk.</p>
				</td>
			</tr>
			<tr class="zrf_attachments_row" style="display: none">
				<th scope="row"><label for="zrf_attachments_type">Restrict attachent file types</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="text" id="zrf_attachments_type" name="zrf_attachments_type" value=".jpg,.jpeg,.png,.gif,.pdf,.txt,.csv,.xls,.xlsx,.doc,.docx" class="widefat"><p class="zrf_infotab_reveal">A list of file types which the user is allowed to upload to the ticket. For example ".csv,.jpg,.png,.gif".</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_useragent">Include <a href="http://www.whoishostingthis.com/tools/user-agent/" target="_blank">User Agent</a>?</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="checkbox" id="zrf_useragent" name="zrf_useragent" value='true' checked><p class="zrf_infotab_reveal">Enabling this option will add the user's web browser information to the bottom of the ticket description text. This will be visible to the user also.</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_desc_size">Description field size</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="number" id="zrf_desc_size" name="zrf_desc_size" min="1" max="10" value="8" class="widefat"><p class="zrf_infotab_reveal">Controls the size of the description field in the form.</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_assignee">Default Assignee</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="number" id="zrf_assignee" name="zrf_assignee" class="widefat"><p class="zrf_infotab_reveal">You can use this option to automatically assign tickets to a specific agent when submitted via this form. Enter the agent's user ID number.</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_cc">CC Email</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="email" id="zrf_cc" name="zrf_cc" class="widefat"><p class="zrf_infotab_reveal">Sends the ticket to this email address in addition to Zendesk.</p></td>
			</tr>
			<tr>
				<th scope="row"><label for="zrf_redirect">Redirect to URL</label> <span class="dashicons dashicons-info zrf_infotab_icon"></span></th>
				<td><input type="url" id="zrf_redirect" name="zrf_redirect" class="widefat"><p class="zrf_infotab_reveal">The user will be redirected to this link when they successfully submit this form. You can add the shortcode [zrf_ticket_num] to that page and it will display the resulting ticket number from Zendesk.</p></td>
			</tr>
		</table>
	</div> <!--// .card -->
	
	
	
	<script>
	jQuery(document).ready(function($) {
		
		$('#zrf_priority').select2();
		
		$('.zrf_infotab_icon').click(function() {
			$(this).closest('tr').find('.zrf_infotab_reveal').slideDown(250);
		});
		
		if ($("#zrf_subject_on").is(":checked")) {
			$(".zrf_subject_row").show();
		} else {
			$(".zrf_subject_row").hide();
		}
		
		if ($("#zrf_attachments").is(":checked")) {
			$(".zrf_attachments_row").show();
		} else {
			$(".zrf_attachments_row").hide();
		}
		
		if (!$("#zrf_useragent").is(":checked")) {
			output += ' useragent="no"';
		}
		
		$('#shortcode-attributes .form-table').change('input', function() {
			
			var output = '[zendesk_request_form';
			
			if ($("#zrf_split_name").is(":checked")) {
				output += ' split_name="yes"';
			}
			
			if ($("#zrf_subject_on").is(":checked")) {
				$(".zrf_subject_row").show();
				if ($("#zrf_subject").val()) {
					output += ' subject="'+$("#zrf_subject").val()+'"';
				}
				if ($("#zrf_subject_prefix").val()) {
					output += ' subject_prefix="'+$("#zrf_subject_prefix").val()+'"';
				}
			} else {
				$(".zrf_subject_row").hide();
				output += ' subject="no"';
			}
			
			if ($("#zrf_attachments").is(":checked")) {
				$(".zrf_attachments_row").show();
				output += ' attachments="1"';
				if ($("#zrf_attachments_size").val() != 1) {
					output += ' attachments_max_size="'+$("#zrf_attachments_size").val()+'"';
				}
				if ($("#zrf_attachments_type").val() != '.jpg,.jpeg,.png,.gif,.pdf,.txt,.csv,.xls,.xlsx,.doc,.docx') {
					output += ' attachments_allowed="'+$("#zrf_attachments_type").val()+'"';
				}
			} else {
				$(".zrf_attachments_row").hide();
			}
			
			if ($("#zrf_subject").val()) {
				$("#zrf_subject_row_prefix").hide();
			}
			
			
			if ($("#zrf_priority").val().length !== 0) {
				output += ' priority="'+$("#zrf_priority").val()+'"';
			}
			
			
			if (!$("#zrf_useragent").is(":checked")) {
				output += ' useragent="no"';
			}
			
			if ($("#zrf_group").val()) {
				output += ' group="'+$("#zrf_group").val()+'"';
			}
			
			if ($("#zrf_desc_size").val() && $("#zrf_desc_size").val() != 8) {
				output += ' size="'+$("#zrf_desc_size").val()+'"';
			}
			if ($("#zrf_assignee").val()) {
				output += ' assignee="'+$("#zrf_assignee").val()+'"';
			}
			if ($("#zrf_cc").val()) {
				output += ' cc="'+$("#zrf_cc").val()+'"';
			}
			if ($("#zrf_redirect").val()) {
				output += ' redirect="'+$("#zrf_redirect").val()+'"';
			}
			
			output += ']';
			
			$('#zrf_gen_result').val(output);
		
		});
	});
	</script>
	
	<div id="shortcode-attributes" class="card">
		<h2><span class="dashicons dashicons-edit"></span> Extra Shortcode Options</h2>
		<p>Some optional attributes can be used with the [zendesk_request_form] shortcode.</p>
		<table class="widefat">
			<thead>
			<tr>
				<th class="row-title">Option</th>
				<th>Description</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="row-title"><label for="tablecell">subject<br /></label></td>
				<td>
					<p>Pre-fill the Subject of the message. If the Subject is pre-filled then the user cannot edit the value as it will be hidden.</p>
					<p>Alternatively, you can disable and hide the Subject field by using subject="no".</p>
				</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">description<br /></label></td>
				<td>
					<p>Pre-fill the Description field (the main body of the ticket). If the Description is pre-filled then the user cannot edit the value as it will be hidden.</p>
					<p>Alternatively, you can disable and hide the Description field by using description="no" or make the field optional by using description="optional".</p>
				</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">size<br /></label></td>
				<td>Changes the size of the main Description field text area.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">subject_prefix<br /></label></td>
				<td>Can be used to add text to the front of the subject when sent to Zendesk. e.g. subject_prefix="Customer Complaint" would add "Customer Complaint" to the front of the ticket subject.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">priority<br /></label></td>
				<td>This option can be set as a single priority or a list of options. For example, setting this to "high" would set all tickets submitted via the form to High priority. Setting this option as "normal,high,urgent" would give the user those 3 options to choose from in the form.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">attachments<br /></label></td>
				<td>This setting will allow users to upload file attachments with the ticket. Set this to "1" to allow the user to upload a single file attachment. Set to any other integer to allow multiple file uploads. For example attachments="3" would allow the user to upload 3 files.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">attachments_allowed<br /></label></td>
				<td>When attachments are enabled, you can restrict the file types using this option. For example ".gif,.pdf,.txt,.csv". The default allowed files are ".jpg,.jpeg,.png,.gif,.pdf,.txt,.csv,.xls,.xlsx,.doc,.docx"</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">attachments_max_size<br /></label></td>
				<td>When attachments are enabled, you can restrict the maximum file size in megabytes. List the integer only, for example attachments_max_size="7". Possible values are: "1", "7" or "20".</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">attachments_required<br /></label></td>
				<td>Set this to "yes" to force the user to attach a file.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">assignee<br /></label></td>
				<td>Requests created via this form will be automatically assigned to this User/Agent ID.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">group<br /></label></td>
				<td>After you have created a "Custom Form Group", you can assign it to a specific form by using this attribute. Use the "slug" of the group you have created.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">new_window<br /></label></td>
				<td>Set this to "yes" and the form will open a new window when submitted by the user.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">label_email<br /></label></td>
				<td>Override the default "Your Email" label on the Email field.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">label_name<br /></label></td>
				<td>Override the default "Your Name" label on the form.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">label_subject<br /></label></td>
				<td>Override the default "Subject" label on the Subject field.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">label_description<br /></label></td>
				<td>Override the default "Your Message" label on the Description field.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">label_submit<br /></label></td>
				<td>Override the default "Submit" label on the Submit button.</td>
			</tr>
			<tr class="alternate">
				<td class="row-title"><label for="tablecell">label_priority<br /></label></td>
				<td>Override the default "Priority of your message" label on the Priority field.</td>
			</tr>
			<tr>
				<td class="row-title"><label for="tablecell">redirect<br /></label></td>
				<td>Full URL to redirect user to after sucessfully submitting the form.<br />Add the shortcode [zrf_ticket_num] to that page and it will display the resulting ticket number from Zendesk (<a href="https://wordpress.org/support/topic/how-to-change-success-message-color/#post-9484905" target="_blank">more info</a>).</td>
			</tr>
			</tbody>
		</table>
		<h3>Example Shortcode</h3>
		<p>The shortcode below will display a form with the following options:</p>
		<ul style="list-style:initial;margin-left:20px;">
			<li>The description field will be size 10.</li>
			<li>The subject will be prefixed with "Customer Complaint - " when sent to Zendesk.</li>
			<li>This form will include the custom fields from the "complaints" group.</li>
			<li>User will be redirected to <em>http://example.com/complaint-received</em> on submission.</li>
		</ul>
		<textarea class="widefat">[zendesk_request_form size="10" subject_prefix="Customer Complaint - " group="complaints" redirect="http://example.com/complaint-received"]</textarea>

	</div><!--// .card -->
	
	<div class="card">
		<h2><span class="dashicons dashicons-heart"></span> Share some Zen!</h2>
		<p>We have spent many, many hours developing this plugin for our own support system. It seemed like a waste if we kept it to ourselves, so we are sharing it for <b>free</b>. If this plugin has helped you, then you may wish to say thanks by leaving a <a href="<?php echo esc_url('https://wordpress.org/support/view/plugin-reviews/zendesk-request-form?rate=5#postform'); ?>">5 star rating</a> on WordPress.org :]</p>
		<p>If you have any suggestions to improve the plugin you are also welcome to ask on the <a href="<?php echo esc_url('https://wordpress.org/support/plugin/zendesk-request-form'); ?>">Support Forum</a>.</p>
	</div><!--// .card -->
	
	</div><!--// .wrap -->
	<?php
}

function zrf_connection_tester_callback() {
	
	check_ajax_referer('zrf_check_nonce', 'sec');
	
	$zendesk_domain = sanitize_text_field($_POST['domain']);
	$zendesk_user = sanitize_text_field($_POST['user']);
	$zendesk_token = sanitize_text_field($_POST['token']);
	
	if (empty($zendesk_domain)) {
		echo '<span class="dashicons dashicons-no"></span> Error! Please check you have entered your "Zendesk Subdomain" in the settings above.';
		wp_die();
	}
	if (empty($zendesk_user)) {
		echo '<span class="dashicons dashicons-no"></span> Error! Please check you have entered your "Zendesk Admin Email" in the settings above.';
		wp_die();
	}
	if (empty($zendesk_token)) {
		echo '<span class="dashicons dashicons-no"></span> Error! Please check you have entered your "Zendesk API Token" in the settings above.';
		wp_die();
	}
	
	$url = esc_url('https://'.$zendesk_domain.'.zendesk.com/api/v2/tickets.json');

	$headers = array(
		'Authorization' => 'Basic '.base64_encode($zendesk_user.'/token:'.$zendesk_token)
	);
	
	$args = array(
		'method' => 'GET',
		'timeout' => 15,
		'redirection' => 10,
		'blocking' => true,
		'headers' => $headers,
	);
	
	$result_msg = '';
	
	$response = wp_safe_remote_request($url, $args);
	if (is_wp_error($response)) {
		$error_message = strip_tags($response->get_error_message());
		$result_msg = '<span class="piperror"><span class="dashicons dashicons-no"></span> Error! Response from your server: "'.$error_message.'". Please contact your web host for assistance.</span>';
	} elseif (is_array($response)) {
		$code = intval($response['response']['code']);
		if ($code === 200) {
			$result_msg = '<span class="pipsuccess"><span class="dashicons dashicons-yes"></span> Success! You can add a form to any page with the [zendesk_request_form] shortcode</span>';
		} elseif ($code === 404) {
			$result_msg = '<span class="piperror"><span class="dashicons dashicons-no"></span> Error! Invalid Zendesk Subdomain.</span>';
		} else {
			$result_msg = '<span class="piperror"><span class="dashicons dashicons-no"></span> Error! Response code from Zendesk is <a href="https://developer.zendesk.com/rest_api/docs/core/introduction#response-format" target="_blank" style="color: red">'.$code.'</a>.<br />Check that you have entered the correct Admin Email.<br />Check that you have copied the correct Zendesk <a href="https://developer.zendesk.com/rest_api/docs/core/introduction#api-token" target="_blank">API Token</a>.</span>';
		}
	} else {
		$result_msg = '<span class="piperror"><span class="dashicons dashicons-no"></span> Error! Could not connect to Zendesk API. Does your host have cURL enabled on your server?</span>';
	}
	
	echo $result_msg;

	wp_die();
}
add_action('wp_ajax_zrf_connection_tester', 'zrf_connection_tester_callback');