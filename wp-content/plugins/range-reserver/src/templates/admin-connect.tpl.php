<?php
wp_localize_script('rrc-connect-admin', 'twilio_template_canceled', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_CANCELED));
wp_localize_script('rrc-connect-admin', 'twilio_template_confirmed', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_CONFIRMED));
wp_localize_script('rrc-connect-admin', 'twilio_template_pending', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_PENDING));
wp_localize_script('rrc-connect-admin', 'twilio_template_reservation', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_RESERVATION));
wp_localize_script('rrc-connect-admin', 'twilio_template_reminder', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_REMINDER));
wp_localize_script('rrc-connect-admin', 'twilio_template_follow', get_option('RRC_' . RRC_Twilio_Fields::TEMPLATE_FOLLOW));
?>
<script>
    window.rrData = {};
    var rr = window.rrData;

    rr.Locations = <?php echo $this->models->get_pre_cache_json('rr_locations'); ?>;
    rr.Bays = <?php echo $this->models->get_pre_cache_json('rr_bays'); ?>;
    rr.Lanes = <?php echo $this->models->get_pre_cache_json('rr_lanes'); ?>;
    rr.MetaFields = <?php echo $this->models->get_pre_cache_json('rr_meta_fields', array('position' => 'ASC')); ?>;


    // woo commerce
    var woo_products = <?php echo get_option('RRC_' . RRC_Woo_Fields::PRODUCTS, '[]'); ?>;
    var woo_status = <?php echo get_option('RRC_' . RRC_Woo_Fields::STATUS, 0); ?>;


</script>

<script id="rrc-settings-main" type="text/template">
    <div class="connect-container wrap">
        <?php if (!defined('DISABLE_WP_CRON') || DISABLE_WP_CRON === false) : ?>
            <div class="notice notice-warning is-dismissible">
                <p>Your <strong>Cron</strong> is running inside WordPress! Please consider setting <strong>Cron</strong>
                    to run from <strong>System Task Scheduler</strong>! By doing that all background task are going to
                    execute on time and without any limitation. Please follow official WordPress guide on <a
                            href="https://developer.wordpress.org/plugins/cron/hooking-into-the-system-task-scheduler/"
                            target="_blank">Hooking WP-Cron Into the System Task Scheduler</a>.</p>
            </div>
        <?php endif; ?>
        <?php if (!$is_cron_running) : ?>
            <div class="notice notice-warning is-dismissible">
                <p>Your <strong>Cron</strong> background tasks are not running properly! <?php echo $cron_message; ?>!</p><p> Please follow official WordPress guide on <a
                            href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/"
                            target="_blank">Hooking WP-Cron Into the System Task Scheduler</a>.</p>
            </div>
        <?php endif; ?>
        <ul id="tab-header" class="connects">
            <li class="connect tab-selected" data-connect="google">
                <a href="#google">
                    <i class="icon-googlecalendar"></i>
                    <span><?php _e('Google Cal', 'range-reserver-connect'); ?></span>
                </a>
            </li>
            <li class="connect tab-selected" data-connect="icalendar">
                <a href="#icalendar">
                    <i class="icon-icalendar"></i>
                    <span><?php _e('iCalendar', 'range-reserver-connect'); ?></span>
                </a>
            </li>
            <li class="connect" data-connect="twilio">
                <a href="#twilio">
                    <i class="icon-sms"></i>
                    <span><?php _e('Messages', 'range-reserver-connect'); ?></span>
                </a>
            </li>
            <li class="connect" data-connect="paypal">
                <a href="#paypal">
                    <i class="icon-money-2"></i>
                    <span>PayPal</span>
                </a>
            </li>
            <li class="connect" data-connect="slack" style="display: none;">
                <a class="disabled">
                    <i class="fa fa-slack"></i>
                    <span><?php _e('Slack', 'range-reserver-connect'); ?></span>
                </a>
            </li>
            <li class="connect" data-connect="facebook" style="display: none;">
                <a class="disabled">
                    <i class="fa fa-facebook-square"></i>
                    <span><?php _e('Facebook', 'range-reserver-connect'); ?></span>
                </a>
            </li>
            <li class="connect" data-connect="woo">
                <a href="#woo">
                    <i class="icon-commerce"></i>
                    <span><?php _e('WooCommerce', 'range-reserver-connect'); ?></span>
                </a>
            </li>
        </ul>
        <!-- MAIN PAGE CONTENT -->
        <div id="tab-content"></div>
    </div>
</script>

<script id="rrc-tpl-google" type="text/template">
    <div id="connect-content">
        <p><?php _e('Before authorization please create and provide Google Client data (id, secret token). For more infomation please watch ' . '<a href="https://www.youtube.com/watch?v=3nNa-NvzduY" target="_blank"> >> VIDEO TUTORIAL <<</a> .', 'range-reserver-connect'); ?></p>
        <table class="form-table form-table-translation">
            <tbody>
            <tr>
                <th class="row">Host :</th>
                <td><?php echo RangeReserver::get_base_host_url(); ?></td>
            </tr>
            <tr>
                <th class="row">Redirect :</th>
                <td><?php echo RangeReserver::get_redirect_url(); ?></td>
            </tr>
            <tr>
                <th class="row">Redirect :</th>
                <td><?php echo RangeReserver::get_redirect_url(); ?>&amp;</td>
            </tr>
            </tbody>
        </table>
        <hr class="divider">
        <br>
        <table class="form-table form-table-translation">
            <tbody>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Client ID', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="gci" class="field" name="gci" autocomplete="off" type="text" style="width: 50%"><br>
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for="google_client_secret"><?php _e('Client secret', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="gcs" class="field" name="gcs" autocomplete="off" type="password" style="width: 50%">
                    <a id="toggle-google-secret" href="#"
                       data-text="<?php _e('hide', 'range-reserver-connect'); ?>"><?php _e('show', 'range-reserver-connect'); ?></a>
                    <br>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <button class="button button-primary save-google-settings" style="float:right; margin-right: 140px;"><?php _e('Save', 'range-reserver-connect'); ?></button>
            </p>
            <br>
        </div>
        <div>
            <hr class="divider">
            <p>
            <div style="float:right; margin-right: 140px;">
                <button id="google-authorize"
                        class="button button-primary"><?php _e('Authorize', 'range-reserver-connect'); ?></button>
                <button id="delete-google-token"
                        class="button"><?php _e('Remove token', 'range-reserver-connect'); ?></button>
            </div>
            <?php _e('Please authorize with your Google account in order to get access to adding appointments to your Google calendar.', 'range-reserver-connect'); ?>
            </p>
        </div>
        <div>
            <hr class="divider">
        </div>
        <table class="form-table form-table-translation">
            <tbody>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php _e('Use tags for creating customizable subject and description:', 'range-reserver-connect'); ?></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Event subject', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input class="field" name="google_subject" type="text"
                               value="<?php echo esc_attr(get_option('RRC_' . RRCGoogleFields::SUBJECT_TEMPLATE)); ?>"
                               style="width: 70%;">
                        <br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="google_description"><?php _e('Description', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <textarea class="field" name="google_description" style="width: 70%;"><?php echo esc_attr(get_option('RRC_' . RRCGoogleFields::DESCRIPTION_TEMPLATE, '')); ?></textarea>
                        <br>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <small><?php _e('Tags', 'range-reserver-connect'); ?> : #id#, #date#, #start#, #end#, #status#,
                            #created#, #price#, #ip#, #link_confirm#, #link_cancel#, #url_confirm#, #url_cancel#, #bay_name#, #bay_duration#,
                            #bay_price#, #worker_name#, #worker_email#, #worker_phone#, #location_name#,
                            #location_address#, #location_location#
                        </small>
                        <br>
                        <small><?php _e('Custom fields', 'range-reserver-connect'); ?>
                            : <?php echo implode(', ', RRCUtils::get_custom_fields_tags()); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><?php _e('Set default values for sync new Appointments created on Google Calendar', 'range-reserver-connect'); ?></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Default location', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="rrc-default-location" class="field" name="default_location">
                        </select>
                        <br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Default bay', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="rrc-default-bay" class="field" name="default_bay"></select>
                        <br>
                        <small><?php _e('leave empty if you want to turn off sync back new Appointments from Google', 'range-reserver-connect'); ?></small>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Use customer email', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="google_use_customer_email" class="field" data-key="google_use_customer_email" name="google_use_customer_email"
                               type="checkbox" <% if (google_use_customer_email == "1") { %>checked <% } %>>
                        <br>
                        <small><?php _e('mark this option if you want to set customers email inside Google Calendar Event as attendee', 'range-reserver-connect'); ?></small>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Copy previous fields', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="google_use_previous_fields" class="field" data-key="google_use_previous_fields" name="google_use_previous_fields"
                               type="checkbox" <% if (google_use_previous_fields == "1") { %>checked <% } %>>
                        <br>
                        <small><?php _e('mark this option if you want to copy previous fields when importing new events Created on Google Calendar side. It is based on matching email with EA and values are pulled from latest event with such email. This will trigger customers email notification on import.', 'range-reserver-connect'); ?></small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Sync data for next', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input class="field" name="google_sync_days" type="number"
                               value="<?php echo get_option('RRC_' . RRCGoogleFields::SYNC_DAYS); ?>"
                               style="width: 80px;"> <?php _e('days', 'range-reserver-connect'); ?><br>
                        <small><?php _e('if you want to sync all events se to 0', 'range-reserver-connect'); ?> </small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Sync interval', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="rrc-sync-interval" class="field" name="google_sync_interval">
                            <option value="5">5 <?php _e('minutes', 'range-reserver-connect'); ?></option>
                            <option value="15">15 <?php _e('minutes', 'range-reserver-connect'); ?></option>
                            <option value="30">30 <?php _e('minutes', 'range-reserver-connect'); ?></option>
                        </select>
                        <br>
                        <small><?php _e('how often to run sync job', 'range-reserver-connect'); ?> </small>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <i><?php _e('Name, Email, Description fields will be auto populated during sync from new Google Events created inside Calendar.', 'range-reserver-connect'); ?></i>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <th><?php _e('Advanced Calendars sync', 'range-reserver-connect'); ?> :</th>
                    <td>
                    </td>
                </tr>
            </tbody>
        </table>
        <p><button id="add-google-calendar" class="button button-primary">Add calendar</button></p>
        <table class="wp-list-table widefat fixed">
            <thead>
            <tr>
                <th class="manage-column column-title"><?php _e('Calendar', 'range-reserver-connect'); ?></th>
                <th class="manage-column column-title"><?php _e('Location', 'range-reserver-connect'); ?></th>
                <th class="manage-column column-title"><?php _e('Bay', 'range-reserver-connect'); ?></th>
                <th class="manage-column column-title"><?php _e('Worker', 'range-reserver-connect'); ?></th>
                <th class="manage-column column-title"><?php _e('Actions', 'range-reserver-connect'); ?></th>
            </tr>
            </thead>
            <tbody id="google-advanced-calendars">
            </tbody>
        </table>
        <i>* <?php _e('If you want to sync only with Primary Google calendar leave Advanced Calendars sync table empty.', 'range-reserver-connect'); ?> </i><br>
        <i>* <?php _e('For syncing the new Events created in Google calendar, last row for particular calendar will be used. Any field will be translated to Default Location and Bay during sync back.', 'range-reserver-connect'); ?> </i><br>
        <table class="form-table form-table-translation">
            <tbody>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Delete from GCal will', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="google_delete_strategy" class="field" name="google_delete_strategy" style="width: 200px;">
                            <option value="0">Cancel Appointment in EA</option>
                            <option value="1">Delete Appointment in EA</option>
                        </select><br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Log level', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="google_sync_log_level" class="field" name="google_sync_log_level" style="width: 200px;">
                            <option value="debug">Debug + Info + Error</option>
                            <option value="info">Info + Error</option>
                            <option value="error">Error</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Log files', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td><button id="google-download-sync-log" class="button">Download sync log file</button></td>
                </tr>
            </tbody>
        </table>
        <div>
            <br>
            <br>
            <p>
                <button class="button button-primary save-google-settings" style="float:right; margin-right: 140px;">
                    Save
                </button>
            </p>
            <br>
            <br>
        </div>
    </div>
    <div id="google-calendar-dialog" style="display: none;">
        <div id="google-calendar-dialog-content"></div>
    </div>
</script>

<script id="rrc-tpl-google-calendar" type="text/template">
    <div>
        <div>
            <label style="display: inline-block; width: 30%;">Google Calendar</label>
            <select id="google-calendar-name" class="field" data-field="calendar" style="width: 65%;">
                <% _.each(calendars, function(i) { %>
                <option value="<%= i.id %>"><%= i.name %></option>
                <% }); %>
            </select>
        </div>
        <div>
            <label style="display: inline-block; width: 30%;">Location</label>
            <select id="google-calendar-location" class="field" data-field="location" style="width: 65%;">
                <option value="*">Any</option>
                <% _.each(rr.Locations, function(i) { %>
                <option value="<%= i.id %>"><%= i.name %></option>
                <% }); %>
            </select>
        </div>
        <div>
            <label style="display: inline-block; width: 30%;">Bay</label>
            <select id="google-calendar-bay" class="field" data-field="bay" style="width: 65%;">
                <option value="*">Any</option>
                <% _.each(rr.Bays, function(i) { %>
                <option value="<%= i.id %>"><%= i.name %></option>
                <% }); %>
            </select>
        </div>
        <div>
            <label style="display: inline-block; width: 30%;">Worker</label>
            <select id="google-calendar-worker" class="field" data-field="worker" style="width: 65%;">
                <option value="*">Any</option>
                <% _.each(rr.Lanes, function(i) { %>
                <option value="<%= i.id %>"><%= i.name %></option>
                <% }); %>
            </select>
        </div>
        <hr>
        <div style="float: right">
            <button id="save-google-calendar-dialog" class="button button-primary">Save</button>
        </div>
    </div>
</script>

<script id="rrc-tpl-google-calendar-rows" type="text/template">
    <% _.each(calendars, function(element, index) { %>
    <tr>
        <% _.each(element, function(column) { %>
        <td><%= column.name %></td>
        <% }); %>
        <td><button class="button remove-google-calendar-row" data-index="<%= index %>">Remove</button></td>
    </tr>
    <% }); %>
</script>

<script id="rr-tpl-twilio" type="text/template">
    <div id="connect-content">
        <h1>Message API access</h1>
        <table class="form-table form-table-translation">
            <tbody>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Message provider', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="message-provider" class="field" name="message-provider" style="width: 200px">
                            <option value="twilio"><?php _e('Twilio [SMS]', 'range-reserver-connect'); ?></option>
                            <option value="omnicom"><?php _e('Omnicom GSM [SMS]', 'range-reserver-connect'); ?></option>
                            <option value="mail"><?php _e('Build-in Mail [MAIL]', 'range-reserver-connect'); ?></option>
                        </select>
                        <br>
                        <small><?php _e('select provider type for notification', 'range-reserver-connect'); ?> </small>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table form-table-translation twilio-fields provider-fields" style="display: none">
            <tbody>
                <tr>
                    <th class="row">
                        <label for="twilio_id"><?php _e('Account SID', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="twilio_id" class="field" name="twilio_account_id" type="text" autocomplete="off"
                               value="<?php echo get_option('RRC_' . RRC_Twilio_Fields::ACCOUNT_ID); ?>"
                               style="width: 50%;"><br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="twilio_token"><?php _e('Authtoken', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="twilio_token" class="field" name="twilio_token" type="password" autocomplete="off"
                               value="<?php echo get_option('RRC_' . RRC_Twilio_Fields::TOKEN); ?>" style="width: 50%;">
                        <a id="toggle-twilio-secret" href="#"
                           data-text="<?php _e('hide', 'range-reserver-connect'); ?>"><?php _e('show', 'range-reserver-connect'); ?></a>
                        <br>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table form-table-translation omnicom-fields provider-fields" style="display: none">
            <tbody>
                <tr>
                    <th class="row">
                        <label for="omnicom_username"><?php _e('Username', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="omnicom_username" class="field" name="omnicom_username" type="text" autocomplete="off"
                               value=""
                               style="width: 50%;"><br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="omnicom_api_password"><?php _e('Api password', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="omnicom_api_password" class="field" name="omnicom_api_password" type="text" autocomplete="off"
                               value=""
                               style="width: 50%;"><br>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="omnicom_api_token"><?php _e('Api Token', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="omnicom_api_token" class="field" name="omnicom_api_token" type="text" autocomplete="off"
                               value=""
                               style="width: 50%;"><br>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="form-table form-table-translation mail-fields provider-fields" style="display: none">
            <tbody>
            <tr>
                <th class="row">
                    <label for="mail_subject"><?php _e('Mail subject', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="mail_subject" class="field" name="mail_subject" type="text" autocomplete="off"
                           value=""
                           style="width: 50%;"><br>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2"><small>Fields like Phone field, Send from, Country code will be ignored for email.</small></td>
            </tr>
            </tbody>
        </table>
        <div>
            <hr class="divider">
        </div>
        <table class="form-table form-table-translation">
            <tbody>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Phone field', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <select id="rrc-twilio-phone" class="field" name="twilio_phone_field">
                        <% _.each(rr.MetaFields, function(i) { %>
                        <option value="<%= i.slug %>"><%= i.label %></option>
                        <% }); %>
                    </select>
                    <br>
                    <small><?php _e('field that will be used as phone number for sending SMS to. Customer number', 'range-reserver-connect'); ?> </small>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Send from', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="rrc-twilio-from" class="field" name="twilio_phone_from" type="text"/>
                    <br>
                    <small><?php _e('phone number that will be used. Some providers allow alphanumeric values as sender', 'range-reserver-connect'); ?> </small>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Country number', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="rrc-twilio-country-number" class="field" name="twilio_country_number" type="text"/>
                    <br>
                    <small><?php _e('you can allow customer to enter numbers without country part. This part will be added before number that customer entered. NOTE: leading 0 will be removed. If you don\'t want to use it leave it blank.', 'range-reserver-connect'); ?> </small>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Message Template', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <p>
                        <a class="sms-tab selected"
                           data-textarea="#sms-pending"><?php _e('Pending', 'range-reserver'); ?></a>
                        <a class="sms-tab"
                           data-textarea="#sms-reservation"><?php _e('Reservation', 'range-reserver'); ?></a>
                        <a class="sms-tab"
                           data-textarea="#sms-canceled"><?php _e('Canceled', 'range-reserver'); ?></a>
                        <a class="sms-tab"
                           data-textarea="#sms-confirmed"><?php _e('Confirmed', 'range-reserver'); ?></a>
                        <a class="sms-tab"
                           data-textarea="#sms-reminder"><?php _e('Reminder', 'range-reserver'); ?></a>
                        <a class="sms-tab"
                           data-textarea="#sms-follow"><?php _e('Follow up', 'range-reserver'); ?></a>
                    </p>
                    <textarea id="sms-template" style="height: 150px; width: 60%;" name="sms-template"></textarea>
                    <small> # <span id="sms-template-count"></span></small>
                    <br>
                    <small><?php _e('Tags', 'range-reserver-connect'); ?> : #id#, #date#, #start#, #end#, #status#,
                        #created#, #price#, #ip#, #link_confirm#, #link_cancel#, #url_confirm#, #url_cancel#, #bay_name#, #bay_duration#,
                        #bay_price#, #worker_name#, #worker_email#, #worker_phone#, #location_name#,
                        #location_address#, #location_location#
                    </small>
                    <br>
                    <small><?php _e('Custom fields', 'range-reserver-connect'); ?>
                        : <?php echo implode(', ', RRCUtils::get_custom_fields_tags()); ?>
                </td>
            </tr>
            <tr style="display: none;">
                <td colspan="2">
                    <textarea name="twilio_template_pending" class="field" id="sms-pending"></textarea>
                    <textarea name="twilio_template_reservation" class="field" id="sms-reservation"></textarea>
                    <textarea name="twilio_template_canceled" class="field" id="sms-canceled"></textarea>
                    <textarea name="twilio_template_confirmed" class="field" id="sms-confirmed"></textarea>
                    <textarea name="twilio_template_reminder" class="field" id="sms-reminder"></textarea>
                    <textarea name="twilio_template_follow" class="field" id="sms-follow"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Send to', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <select id="twilio_send_to" class="field" name="twilio_send_to">
                        <option value="1"><?php _e('Customer', 'range-reserver-connect'); ?></option>
                        <option value="2"><?php _e('Employee', 'range-reserver-connect'); ?></option>
                        <option value="3"><?php _e('Customer and Employee', 'range-reserver-connect'); ?></option>
                    </select>
                    <br>
                    <small><?php _e('select who will get notification', 'range-reserver-connect'); ?> </small>
                </td>
            </tr>
            <tr>
                <td colspan="2"><br></td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Send message reminder', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <select class="field" name="twilio_sms_reminder" id="twilio_sms_reminder">
                        <option value="">off</option>
                        <option value="5">5 minutes</option>
                        <option value="10">10 minutes</option>
                        <option value="20">20 minutes</option>
                        <option value="30">30 minutes</option>
                        <option value="40">40 minutes</option>
                        <option value="50">50 minutes</option>
                        <option value="60">60 minutes</option>
                        <option value="120">2 hours</option>
                        <option value="180">3 hours</option>
                        <option value="240">4 hours</option>
                        <option value="300">5 hours</option>
                        <option value="360">6 hours</option>
                        <option value="1440">24 hours</option>
                    </select><br>
                    <small><?php _e('send same message notification before appointment', 'range-reserver-connect'); ?></small>
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Follow Up Message', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <select class="field" name="twilio_sms_follow" id="twilio_sms_follow">
                        <option value="">off</option>
                        <option value="5">5 minutes</option>
                        <option value="10">10 minutes</option>
                        <option value="20">20 minutes</option>
                        <option value="30">30 minutes</option>
                        <option value="40">40 minutes</option>
                        <option value="50">50 minutes</option>
                        <option value="60">60 minutes</option>
                        <option value="120">2 hours</option>
                        <option value="180">3 hours</option>
                        <option value="240">4 hours</option>
                        <option value="300">5 hours</option>
                        <option value="360">6 hours</option>
                        <option value="1440">24 hours</option>
                    </select><br>
                    <small><?php _e('send Follow Up notification after start of appointment plus selected offset', 'range-reserver-connect'); ?></small>
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('limit message per Month', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="twilio-sms-limit-month" class="field" name="twilio_sms_limit_month" type="number" min="0"/>
                    <br>
                    <small><?php _e('limit number of messages per month. Set to 0 or without any value to have no limit. Current Message count for this month is:', 'range-reserver-connect'); ?> <strong><?php echo RRC_Twilio_Logic::count_of_sms_this_month();?></strong></small>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <button class="button button-primary save-twilio-settings" style="float:right; margin-right: 140px;">Save</button>
            </p>
            <br>
            <br>
        </div>
    </div>
</script>

<script id="rr-tpl-woo" type="text/template">
    <div id="connect-content">
        <table class="form-table form-table-translation">
            <tbody>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Add to Cart', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input class="field" data-key="woo_status" name="woo_status" type="checkbox" <% if (woo_status ==
                    "1") { %>checked <% } %>>
                </td>
                <td>
                    <span class="description"> <?php _e('Mark this option if you want to add products to cart on Appointment creation.', 'range-reserver-connect'); ?></span>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <hr class="divider">
        </div>
        <p>Connect bays with Products that will be added to chart after creating Appointments. You can set redirect
            page to Checkout.</p>
        <table class="form-table form-table-translation">
            <thead>
            <tr>
                <th>Bay name</th>
                <th>Product name</th>
            </tr>
            </thead>
            <tbody>
            <% _.each(rr.Bays, function(i) { %>
            <tr>
                <td><%= i.name %></td>
                <td><input data-bay="<%= i.id %>" class="woo-product" type="text"></td>
            </tr>
            <% }); %>
            </tbody>
        </table>
        <div>
            <hr class="divider">
        </div>
        <div>
            <button class="button button-primary save-woo-settings" style="float:right; margin-right: 140px;">Save
            </button>
            </p>
            <br>
            <br>
        </div>
    </div>
</script>

<script id="rr-tpl-icalendar" type="text/template">
    <div id="connect-content">
        <p>iCalendar is a file format which allows your Customers to get meeting requests and import them to Calendar
            easily for example MS Outlook etc. For whole list of Applications that supports iCalendar format check : <a
                    href="https://en.wikipedia.org/wiki/List_of_applications_with_iCalendar_support" target="_blank">LINK</a>
        </p>
        <div>
            <hr class="divider">
        </div>
        <table class="form-table form-table-translation">
            <tbody>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Send ICS file', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="icalendar_ics_send" class="field" data-key="icalendar_ics_send" name="icalendar_ics_send"
                           type="checkbox" <% if (icalendar_ics_send == "1") { %>checked <% } %>>
                </td>
                <td>
                    <span class="description"> <?php _e('Send ICS file along notification email to customers so it can be easily imported to their calendar.', 'range-reserver-connect'); ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Description', 'range-reserver-connect'); ?> :</label>
                </th>
                <td colspan="2">
                    <input id="icalendar_ics_description" class="field" data-key="icalendar_ics_description"
                           name="icalendar_ics_description" type="text" style="width: 70%;" autocomplete="off">
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Summary', 'range-reserver-connect'); ?> :</label>
                </th>
                <td colspan="2">
                    <input id="icalendar_ics_summary" class="field" data-key="icalendar_ics_summary"
                           name="icalendar_ics_summary" type="text" style="width: 70%;" autocomplete="off">
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2">
                    <small><?php _e('Tags', 'range-reserver-connect'); ?> : #id#, #date#, #start#, #end#, #status#,
                        #created#, #price#, #ip#, #link_confirm#, #link_cancel#, #bay_name#, #bay_duration#,
                        #bay_price#, #worker_name#, #worker_email#, #worker_phone#, #location_name#,
                        #location_address#, #location_location#
                    </small>
                    <br>
                    <small><?php _e('Custom fields', 'range-reserver-connect'); ?>
                        : <?php echo implode(', ', RRCUtils::get_custom_fields_tags()); ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">&nbsp;</td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('File name', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="icalendar_ics_name" class="field" data-key="icalendar_ics_name" name="icalendar_ics_name"
                           type="text">
                </td>
                <td><span class="description"> <?php _e('Name of ICS file in attachment.', 'range-reserver-connect'); ?></span>
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Send ICS to Admin/Worker?', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="icalendar_ics_send_worker" class="field" data-key="icalendar_ics_send_worker" name="icalendar_ics_send_worker"
                           type="checkbox" <% if (icalendar_ics_send_worker == "1") { %>checked <% } %>>
                </td>
                <td>
                    <span class="description"> <?php _e('Send ICS file within admin notification.', 'range-reserver-connect'); ?></span>
                </td>
            </tr>
            <tr>
                <th class="row">
                    <label for=""><?php _e('Use offset time in ICS file', 'range-reserver-connect'); ?> :</label>
                </th>
                <td>
                    <input id="icalendar_ics_offset" class="field" data-key="icalendar_ics_offset" name="icalendar_ics_offset"
                           type="checkbox" <% if (icalendar_ics_offset == "1") { %>checked <% } %>>
                </td>
                <td>
                    <span class="description"> <?php _e('By marking this option ICS time of appointment will be calculated as timezone offset instead of UTC time. In order to use this you must select Named timezone inside WP Settings > General!', 'range-reserver-connect'); ?></span>
                </td>
            </tr>
            </tbody>
        </table>
        <div>
            <hr class="divider">
        </div>
        <div>
            <button class="button button-primary save-icalendar-settings" style="float:right; margin-right: 140px;">
                Save
            </button>
            </p>
            <br>
            <br>
        </div>
    </div>
</script>

<script id="rr-tpl-paypal" type="text/template">
    <div id="connect-content">
        <p>Before using PayPal integration you should create an Application inside PayPal for your account. That can be easily done in few click. <a href="https://developer.paypal.com/developer/applications/create" target="_blank">Just follow the link</a></p>
        <div>
            <hr class="divider">
        </div>
        <table class="form-table form-table-translation">
            <tbody>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Use PayPal', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_on" class="field" data-key="paypal_on" name="paypal_on"
                               type="checkbox" <% if (paypal_on == "1") { %>checked <% } %>>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Use PayPal checkout before making Appointments.', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Payment is required', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_required" class="field" data-key="paypal_required" name="paypal_required"
                               type="checkbox" <% if (paypal_required == "1") { %>checked <% } %>>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Mark this field if payment is required.', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Currency', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="paypal_currency" class="field" name="paypal_currency">
                            <option value="AUD">Australian Dollar</option>
                            <option value="BRL">Brazilian Real</option>
                            <option value="CAD">Canadian Dollar</option>
                            <option value="CZK">Czech Koruna</option>
                            <option value="DKK">Danish Krone</option>
                            <option value="EUR">Euro</option>
                            <option value="HKD">Hong Kong Dollar</option>
                            <option value="HUF">Hungarian Forint</option>
                            <option value="INR">Indian rupee</option>
                            <option value="ILS">Israeli New Sheqel</option>
                            <option value="JPY">Japanese Yen</option>
                            <option value="MYR">Malaysian Ringgit</option>
                            <option value="MXN">Mexican Peso</option>
                            <option value="NOK">Norwegian Krone</option>
                            <option value="NZD">New Zealand Dollar</option>
                            <option value="PHP">Philippine Peso</option>
                            <option value="PLN">Polish Zloty</option>
                            <option value="GBP">Pound Sterling</option>
                            <option value="RUB">Russian Ruble</option>
                            <option value="SGD">Singapore Dollar</option>
                            <option value="SEK">Swedish Krona</option>
                            <option value="CHF">Swiss Franc</option>
                            <option value="TWD">Taiwan New Dollar</option>
                            <option value="THB">Thai Baht</option>
                            <option value="USD">U.S. Dollar</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th>
                        <label for=""><?php _e('Mode', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td colspan="2">
                        <select id="paypal_mode" class="field" name="paypal_mode">
                            <option value="production"><?php _e('production', 'range-reserver-connect'); ?></option>
                            <option value="sandbox"><?php _e('sandbox', 'range-reserver-connect'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Sandbox Client ID', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td colspan="2">
                        <input id="paypal_sandbox" class="field" data-key="paypal_sandbox" autocomplete="off"
                               name="paypal_sandbox" type="text" style="width: 70%;">
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Sandbox Secret', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td colspan="2">
                        <input id="paypal_sandbox_secret" class="field" data-key="paypal_sandbox_secret" autocomplete="off"
                               name="paypal_sandbox_secret" type="text" style="width: 70%;">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Production Client ID', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td colspan="2">
                        <input id="paypal_prod" class="field" data-key="paypal_prod" autocomplete="off"
                               name="paypal_prod" type="text" style="width: 70%;">
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Production Secret', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td colspan="2">
                        <input id="paypal_prod_secret" class="field" data-key="paypal_prod_secret" autocomplete="off"
                               name="paypal_prod_secret" type="text" style="width: 70%;">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Label - Pay with PP', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_label_paypal" class="field" name="paypal_label_paypal" autocomplete="off" type="text"><br>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Switch label of selecting PP payment (only available if payment is not mandatory)', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Label - Pay on site', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_label_on_spot" class="field" name="paypal_label_on_spot" autocomplete="off" type="text"><br>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Switch label of selecting pay in store payment (only available if payment is not mandatory)', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr class="divider"></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Use Smart Button', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_smart_button" class="field" data-key="paypal_smart_button" name="paypal_smart_button"
                               type="checkbox" <% if (paypal_smart_button == "1") { %>checked <% } %>>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Use Smart Button instead of legacy checkout button', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr class="divider"></td>
                </tr>
                <tr>
                    <td colspan="3"><h3><?php _e('Legacy settings', 'range-reserver-connect'); ?></h3></td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Allow Payment via Card', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <input id="paypal_allow_card" class="field" data-key="paypal_allow_card" name="paypal_allow_card"
                               type="checkbox" <% if (paypal_allow_card == "1") { %>checked <% } %>>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Allow buyers to pay with their credit or debit card (Visa, Mastercard, American Express, Discover, and so on).', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Button size', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="paypal_button_size" class="field" name="paypal_button_size">
                            <option value="small">Small</option>
                            <option value="medium" selected="">Medium</option>
                            <option value="large">Large</option>
                            <option value="responsive">Repsonsive</option>
                        </select>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Size of checkout button.', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for=""><?php _e('Button color', 'range-reserver-connect'); ?> :</label>
                    </th>
                    <td>
                        <select id="paypal_button_color" class="field" name="paypal_button_color">
                            <option value="gold" selected="">Gold</option>
                            <option value="blue">Blue</option>
                            <option value="silver">Silver</option>
                            <option value="white">White</option>
                            <option value="black">Black</option>
                        </select>
                    </td>
                    <td>
                        <span class="description"> <?php _e('Select color for checkout button.', 'range-reserver-connect'); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <hr class="divider">
        </div>
        <div>
            <button class="button button-primary save-paypal-settings" style="float:right; margin-right: 140px;">Save</button>
            </p>
            <br>
            <br>
        </div>
    </div>
</script>
