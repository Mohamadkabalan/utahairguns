<?php
if (!defined('ABSPATH')) die;

function zrf_remove_cpt_from_menu() {
    remove_menu_page('edit.php?post_type=zrf_custom_field');
}
add_action('admin_menu', 'zrf_remove_cpt_from_menu');

// Register Custom Taxonomy to group custom fields n multiple forms
function zrf_create_forms_taxonomy() {

	$labels = array(
		'name'                       => 'Field Groups',
		'singular_name'              => 'Field Group',
		'menu_name'                  => 'Field Groups',
		'all_items'                  => 'All Field Groups',
		'new_item_name'              => 'New Field Group Name',
		'add_new_item'               => 'Add New Field Group',
		'edit_item'                  => 'Edit Field Group',
		'update_item'                => 'Update Field Group',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy('zrf_field_group', array('zrf_custom_field'), $args);

}
add_action('init', 'zrf_create_forms_taxonomy', 0);


// Register Custom Post Type
function zendesk_request_form_post_type() {

	$labels = array(
		'add_new_item' => 'Add New Field',
		'new_item' => 'New Field',
		'edit_item' => 'Edit Field',
		'update_item' => 'Update Field',
	);
	$args = array(
		'label' => 'Custom Fields',
		'labels' => $labels,
		'supports' => array('title', 'page-attributes'),
		'taxonomies' => array('zrf_field_group'),
		'hierarchical' => false,
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true, // requited for drag+drop ordering plugins
		'menu_position' => 100,
		'menu_icon' => 'dashicons-list-view',
		'show_in_admin_bar' => false,
		'show_in_nav_menus' => false,
		'can_export' => false,
		'has_archive' => false,		
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'rewrite' => false,
		'capability_type' => 'page',
	);
	register_post_type('zrf_custom_field', $args);

}
add_action('init', 'zendesk_request_form_post_type', 0);



function wpb_change_title_text($title){
 
     if  (get_current_screen()->post_type == 'zrf_custom_field') {
          $title = 'Field name';
     }
 
     return $title;
}
 
add_filter('enter_title_here', 'wpb_change_title_text');



// Don't use Gutenberg https://make.wordpress.org/core/2018/10/30/block-editor-filters/
function pipdig_zrf_fields_dont_use_gutenberg($use_block_editor, $post_type) {
	if ($post_type === 'zrf_custom_field') {
		return false;
	}
	return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'pipdig_zrf_fields_dont_use_gutenberg', 10, 2);


/**
 * Adds a meta box to the field editing screen
 */
function pipdig_zrf_custom_meta() {
	add_meta_box('pipdig_zrf_meta', 'Settings', 'pipdig_zrf_meta_callback', 'zrf_custom_field', 'normal', 'high');
}
add_action('add_meta_boxes', 'pipdig_zrf_custom_meta');

/**
 * Outputs the content of the meta box
 */
function pipdig_zrf_meta_callback($post) {
	wp_nonce_field(basename(__FILE__), 'pipdig_zrf_nonce');
	$zrf_meta = get_post_meta($post->ID);
	?>
	<script>
	jQuery(document).ready(function($) {
		
		function zrfCheckFieldType(fieldType) {
			
			if (fieldType == 'text') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_get, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_default').fadeIn(750);
				$('.zrf_field_description, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'date') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_extra_info, .zrf_field_date_format').fadeIn(750);
				$('.zrf_field_description, .zrf_field_options, .zrf_field_pattern, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_desc_suffix').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'textarea') {
				
				$('#field-default').attr('rows', '3');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_default, .zrf_field_extra_info, .zrf_field_desc_suffix').fadeIn(750);
				$('.zrf_field_placeholder, .zrf_field_pattern, .zrf_field_get, .prefix-and-suffix, .zrf_field_description, .zrf_field_options, .zrf_field_extra_info, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'number') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix').fadeIn(750);
				$('.zrf_field_placeholder, .zrf_field_description, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'url') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix').fadeIn(750);
				$('.zrf_field_description, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'email') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix').fadeIn(750);
				$('.zrf_field_description, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'password') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .prefix-and-suffix, .zrf_field_extra_info').fadeIn(750);
				$('.zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .zrf_field_description, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'hidden') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_id, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_desc_suffix').fadeIn(750);
				$('.zrf_field_title, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_description, .zrf_field_extra_info, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-title, #field-options').removeAttr('required');
				$('#field-id').prop('required',true);
				
			} else if (fieldType == 'descriptive') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_description').fadeIn(750);
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-title, #field-id, #field-options').removeAttr('required');
				$('#field-description').prop('required',true);
				
			} else if (fieldType == 'checkbox') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_extra_info, .zrf_field_required').fadeIn(750);
				$('.zrf_field_description, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .prefix-and-suffix, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeOut(250);
				$('#field-options').removeAttr('required');
				$('#field-title, #field-id').prop('required',true);
				
			} else if (fieldType == 'dropdown') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_extra_info, .zrf_field_required, .zrf_field_options').fadeIn(750);
				$('.zrf_field_description, .zrf_field_placeholder, .prefix-and-suffix, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_get, .zrf_field_default, .zrf_field_date_format').fadeOut(250);
				$('#field-title, #field-id, #field-options').prop('required',true);
				
			} else if (fieldType == 'attachment') {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_extra_info, .zrf_field_required').fadeIn(750);
				$('.zrf_field_id, .zrf_field_description, .zrf_field_options, .zrf_field_placeholder, .prefix-and-suffix, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_get, .zrf_field_default, .zrf_field_date_format').fadeOut(250);
				$('#field-options, #field-id').removeAttr('required');
				$('#field-title').prop('required',true);
				
			} else {
				
				$('#field-default').attr('rows', '1');
				$('.zrf_field_title, .zrf_field_id, .zrf_optional_title, .zrf_field_required, .zrf_field_placeholder, .zrf_field_default, .zrf_field_get, .zrf_field_description, .prefix-and-suffix, .zrf_field_extra_info, .zrf_field_desc_suffix, .zrf_field_pattern, .zrf_field_options, .zrf_field_date_format').fadeIn(250);
				
			}
			
		} // close zrfCheckFieldType function
		
		// check on initial page load
		var fieldType = $('#field-type').find("option:selected").attr('value');
		zrfCheckFieldType(fieldType);
		
		// check on input change
		$('#field-type').change(function(){
			var fieldType = $(this).find("option:selected").attr('value');
			zrfCheckFieldType(fieldType);
		});
		
	});
	</script>
	<style>
	#message a, #edit-slug-box {display:none!important}
	.zrf_field_description {display:none}
	</style>
	<?php global $zrf_allowedtags; ?>
	<div id="zrf-meta-form">
	<h3 class="zrf_required_title">Required settings</h3>
	<p class="zrf_field_type">
		<label for="field-type">Field type</label><br />
		<select name="field-type" id="field-type">
			<option value="text" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'text'); ?>>Text</option>';
			<option value="textarea" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'textarea'); ?>>Textarea</option>';
			<option value="checkbox" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'checkbox'); ?>>Checkbox</option>';
			<option value="number" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'number'); ?>>Number</option>';
			<option value="url" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'url'); ?>>URL</option>';
			<option value="email" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'email'); ?>>Email</option>';
			<option value="password" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'password'); ?>>Password</option>';
			<option value="date" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'date'); ?>>Date</option>';
			<option value="dropdown" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'dropdown'); ?>>Dropdown</option>';
			<!--<option value="attachment" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'attachment'); ?>>File Upload</option>';-->
			<option value="hidden" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'hidden'); ?>>Hidden (Pre-fill a "Default Value" using the option below)</option>';
			<option value="descriptive" <?php if (isset($zrf_meta['field-type'])) selected($zrf_meta['field-type'][0], 'descriptive'); ?>>Descriptive information or Arbitrary text/html</option>';
		</select>
	</p>
	<p class="zrf_field_title">
		<label for="field-title">Title (Displayed to user)</label><br />
		<input type="text" name="field-title" id="field-title" spellcheck="true" value="<?php if (isset($zrf_meta['field-title'])) echo esc_attr($zrf_meta['field-title'][0]); ?>" required />
	</p>
	<p class="zrf_field_id">
		<label for="field-id">Zendesk Ticket Field ID</label><br />
		<input type="number" name="field-id" id="field-id" value="<?php if (isset($zrf_meta['field-id'])) echo sanitize_text_field($zrf_meta['field-id'][0]); ?>" required />
		<br /><span style="font-size: 90%;font-style: italic;">You can find this in your Zendesk dashboard when you edit a ticket field - <a href="https://i.imgur.com/uyg8XHs.png" target="_blank">https://i.imgur.com/uyg8XHs.png</a></span>
	</p>
	<p class="zrf_field_options">
		<label for="field-options">Options - One per line in "tag|Title" pairs. <a href="https://imgur.com/a/OxHIe" target="_blank">Click here</a> for an example</label><br />
		<textarea name="field-options" id="field-options" rows="8" cols="60" style="max-width:100%" required><?php if (isset($zrf_meta['field-options'])) echo esc_attr($zrf_meta['field-options'][0]); ?></textarea>
	</p>
	<h3 style="margin-top: 40px;" class="zrf_optional_title">Optional settings</h3>
	<p class="zrf_field_required">
		<label for="field-required">
			<input type="checkbox" name="field-required" id="field-required" value="1" <?php if (isset($zrf_meta['field-required'])) checked($zrf_meta['field-required'][0], 1); ?> />
			This is a required field <a href="http://www.w3schools.com/tags/att_input_required.asp" target="_blank" style="text-decoration: none"><span class="dashicons dashicons-info zrf_infotab_icon"></span></a>
		</label>
	</p>
	<p class="zrf_field_desc_suffix">
		<label for="field-desc-suffix">
			<input type="checkbox" name="field-desc-suffix" id="field-desc-suffix" value="1" <?php if (isset($zrf_meta['field-desc-suffix'])) checked($zrf_meta['field-desc-suffix'][0], 1); ?> />
			Add data from this field to the ticket description (will be visible to user)
		</label>
	</p>
	<p class="zrf_field_date_format">
		<label for="field-date-format">Date Format</label><br />
		<input type="text" class="" name="field-date-format" id="field-date-format" value="<?php if (isset($zrf_meta['field-date-format'])) echo esc_attr($zrf_meta['field-date-format'][0]); ?>" placeholder="yy-mm-dd" />
		<br /><span style="font-size: 90%;font-style: italic;">You can set any date format supported by jQuery Datepicker. Defaults to yy-mm-dd. <a href="http://api.jqueryui.com/datepicker/#utility-formatDate" target="_blank">More info</a>.</span>
	</p>
	<p class="zrf_field_pattern">
		<label for="field-pattern">HTML5 Pattern <a href="http://www.w3schools.com/tags/att_input_pattern.asp" target="_blank" style="text-decoration: none"><span class="dashicons dashicons-info zrf_infotab_icon"></span></a></label><br />
		<input type="text" class="" name="field-pattern" id="field-pattern" value="<?php if (isset($zrf_meta['field-pattern'])) echo esc_attr($zrf_meta['field-pattern'][0]); ?>" />
	</p>
	<p class="zrf_field_extra_info">
		<label for="field-extra">Extra information for user</label><br />
		<input type="text" name="field-extra" id="field-extra" spellcheck="true" value="<?php if (isset($zrf_meta['field-extra'])) echo esc_attr($zrf_meta['field-extra'][0]); ?>" />
		<br /><span style="font-size: 90%;font-style: italic;">Allowed tags: p, a, b, i, em, strong, br, style, div, img, h2, h3, h4</span>
	</p>
	<p class="zrf_field_placeholder">
		<label for="field-placeholder">Placeholder <a href="http://www.w3schools.com/tags/att_input_placeholder.asp" target="_blank" style="text-decoration: none"><span class="dashicons dashicons-info zrf_infotab_icon"></span></a></label><br />
		<input type="text" name="field-placeholder" id="field-placeholder" value="<?php if (isset($zrf_meta['field-placeholder'])) echo esc_attr($zrf_meta['field-placeholder'][0]); ?>" />
	</p>
	<p class="zrf_field_default">
		<label for="field-default">Default value</label><br />
		<textarea rows="1" cols="50" name="field-default" id="field-default"><?php if (isset($zrf_meta['field-default'])) echo esc_attr($zrf_meta['field-default'][0]); ?></textarea>
	</p>
	<p class="zrf_field_get">
		<label for="field-get">GET value by url query</label><br />
		<input type="text" name="field-get" id="field-get" value="<?php if (isset($zrf_meta['field-get'])) echo esc_attr($zrf_meta['field-get'][0]); ?>" />
		<br /><span style="font-size: 90%; font-style: italic;">Pre-fill the value of this field with a GET query from the url.<br />e.g. use "order" to prefill the field with "1234" if you open the page via http://example.com/page/?order=1234.</span>
	</p>
	<p class="zrf_field_description">
		<label for="field-description">Description text/html</label><br />
		<textarea rows="4" cols="50" name="field-description" id="field-description"><?php if (isset($zrf_meta['field-description'])) echo esc_attr($zrf_meta['field-description'][0]); ?></textarea>
		<br /><span style="font-size: 90%;font-style: italic;">Allowed tags: p, a, b, i, em, strong, br, style, div, img, h2, h3, h4</span>
	</p>
	
	<div class="prefix-and-suffix">
		<h3 style="margin-top: 40px;">Change data before sending to Zendesk</h3>
		<p>The options below can be used to change the way that the data is received in your Zendesk dashboard.</p>
		<p>For example, you may wish to change a custom field into a url that links to your e-commerce system so that you can view the an order. Let's say this field is an Order Number that the user has filled as "1234". We can then take this value and prefix it with "https://example.com/order-details.php?order=".</p>
		<p class="zrf_field_prefix">
			<label for="field-prefix">Prefix data with:</label><br />
			<input type="text" name="field-prefix" id="field-prefix" value="<?php if (isset($zrf_meta['field-prefix'])) echo esc_attr($zrf_meta['field-prefix'][0]); ?>" />
		</p>
		<p class="zrf_field_suffix">
			<label for="field-suffix">Suffix data with:</label><br />
			<input type="text" name="field-suffix" id="field-suffix" value="<?php if (isset($zrf_meta['field-suffix'])) echo esc_attr($zrf_meta['field-suffix'][0]); ?>" />
		</p>
	</div>
	
	</div>
	<?php
	//wp_editor('', 'zrf_section_text', array('textarea_name' => 'content', 'media_buttons' => false));
}

/**
 * Saves the custom meta input
 */
function pipdig_zrf_meta_save($post_id) {
	
	global $zrf_allowedtags;
 
	// Checks save status
	$is_autosave = wp_is_post_autosave($post_id);
	$is_revision = wp_is_post_revision($post_id);
	$is_valid_nonce = (isset($_POST['pipdig_zrf_nonce']) && wp_verify_nonce($_POST['pipdig_zrf_nonce'], basename(__FILE__))) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ($is_autosave || $is_revision || !$is_valid_nonce) {
		return;
	}
 
	// Checks for input and sanitizes/saves if needed
	if (isset($_POST['field-id'])) {
		update_post_meta($post_id, 'field-id', sanitize_text_field($_POST['field-id']));
	}
	if (isset($_POST['field-title'])) {
		update_post_meta($post_id, 'field-title', sanitize_text_field($_POST['field-title']));
	}
	if (isset($_POST['field-type'])) {
		update_post_meta($post_id, 'field-type', sanitize_text_field($_POST['field-type']));
	}
	if (isset($_POST['field-placeholder'])) {
		update_post_meta($post_id, 'field-placeholder', sanitize_text_field($_POST['field-placeholder']));
	}
	if (isset($_POST['field-default'])) {
		update_post_meta($post_id, 'field-default', esc_attr($_POST['field-default']));
	}
	if (isset($_POST['field-get'])) {
		update_post_meta($post_id, 'field-get', sanitize_text_field($_POST['field-get']));
	}
	if (isset($_POST['field-prefix'])) {
		update_post_meta($post_id, 'field-prefix', sanitize_text_field($_POST['field-prefix']));
	}
	if (isset($_POST['field-date-format'])) {
		update_post_meta($post_id, 'field-date-format', sanitize_text_field($_POST['field-date-format']));
	}
	if (isset($_POST['field-pattern'])) {
		update_post_meta($post_id, 'field-pattern', sanitize_text_field($_POST['field-pattern']));
	}
	if (isset($_POST['field-options'])) {
		update_post_meta($post_id, 'field-options', strip_tags($_POST['field-options']));
	}
	if (isset($_POST['field-extra'])) {
		update_post_meta($post_id, 'field-extra', wp_kses($_POST['field-extra'], $zrf_allowedtags));
	}
	if (isset($_POST['field-suffix'])) {
		update_post_meta($post_id, 'field-suffix', sanitize_text_field($_POST['field-suffix']));
	}
	if (isset($_POST['field-description'])) {
		update_post_meta($post_id, 'field-description', wp_kses($_POST['field-description'], $zrf_allowedtags));
	}
	if (isset($_POST['field-required'])) {
		update_post_meta($post_id, 'field-required', 1);
	} else {
		update_post_meta($post_id, 'field-required', '');
	}
	if (isset($_POST['field-desc-suffix'])) {
		update_post_meta($post_id, 'field-desc-suffix', 1);
	} else {
		update_post_meta($post_id, 'field-desc-suffix', '');
	}
 
}
add_action('save_post_zrf_custom_field', 'pipdig_zrf_meta_save');