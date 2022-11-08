<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/jquery.ui.autocomplete.min.js"></script>
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
<script type="text/template" id="rr-settings-main">
<?php 
	get_current_screen()->render_screen_meta();
?>
	<div class="wrap">
		<div id="tab-content">
		</div>
	</div>
</script>

<!--Customize -->
<script type="text/template" id="rr-tpl-custumize">
    <div class="wp-filter">
        <div class="custom-tab-view">
            <!-- TAB SECTION -->
            <div class="tab-selection">
                <div class="tabs-list">
                    <a data-tab="tab-schedules" class="selected" href="#">
                        <span class="icon icon-general"></span><span class="text-label"><?php _e('General', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-mail" href="#">
                        <span class="icon icon-mail"></span><span class="text-label"><?php _e('Mail Notifications', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-full-calendar" href="#">
                      <span class="icon icon-fullcalendar"></span><span class="text-label"><?php _e('FullCalendar Shortcode', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-labels" href="#">
                        <span class="icon icon-label"></span><span class="text-label"><?php _e('Labels', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-date-time" href="#">
                        <span class="icon icon-datetime"></span><span class="text-label"><?php _e('Date & Time', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-fields" href="#">
                        <span class="icon icon-fields"></span><span class="text-label"><?php _e('Custom Form Fields', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-captcha" href="#">
                        <span class="icon icon-recaptcha"></span><span class="text-label"><?php _e('Google reCAPTCHA v2', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-captcha-3" href="#">
                        <span class="icon icon-recaptcha"></span><span class="text-label"><?php _e('Google reCAPTCHA v3', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-form" href="#">
                        <span class="icon icon-redirect"></span><span class="text-label"><?php _e('Form Style & Redirect', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-gdpr" href="#">
                        <span class="icon icon-gdpr"></span><span class="text-label"><?php _e('GDPR', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-money" href="#">
                        <span class="icon icon-money"></span><span class="text-label"><?php _e('Money Format', 'range-reserver'); ?></span>
                    </a>
                    <a data-tab="tab-woocommerce" href="#">
                        <span class="icon icon-commerce"></span><span class="text-label">WooCommerce</span>
                    </a>
                </div>
                <div class="button-wrap">
                    <button class="button button-primary btn-save-settings"><?php _e('Save', 'range-reserver'); ?></button>
                </div>
            </div>

            <div id="tab-schedules" class="form-section">
                <span class="separator vertical"></span>
                <div class="form-container" id="customize-general">
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Busy slots are calculated by same', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('IMPORTANT! This is used to calculate busy slots based on settings that are set here.', 'range-reserver'); ?>"></span>
                        </div>
                        <select id="multiple-work" class="field" data-key="multiple.work" name="multiple.work">
                            <option value="0" data-tip="<?php _e('Use case example: Lane can only provide one bay at the time.', 'range-reserver'); ?>"><?php _e('Lane', 'range-reserver'); ?></option>
                            <option value="2" data-tip="<?php _e('Use case example: Multiple lanes share same location as resource.', 'range-reserver'); ?>"><?php _e('Location', 'range-reserver'); ?></option>
                            <option value="3" data-tip="<?php _e('Use case example: Bay as a shared resource between lanes.', 'range-reserver'); ?>"><?php _e('Bay', 'range-reserver'); ?></option>
                            <option value="1" data-tip="<?php _e('Use case example: Lane can provide different bay at different locations at the same time.', 'range-reserver'); ?>"><?php _e('Lane, Location and Bay', 'range-reserver'); ?></option>
                        </select>
                        <small id="multiple-work-tip"></small>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Compatibility mode', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('If you can\'t EDIT or DELETE conecntion or any other settings, you should mark this option. NOTE: After saving this options you must refresh page!', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="compatibility.mode"
                                   name="compatibility.mode" type="checkbox" <% if
                            (_.findWhere(settings, {rr_key:'compatibility.mode'}).rr_value == "1") {
                            %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Max number of appointments', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Number of appointments that one visitor can make reservation before limit alert is shown. Appointments are counted during one day.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="max.appointments" name="max.appointments"
                               type="text"
                               value="<%= _.findWhere(settings, {rr_key:'max.appointments'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label><?php _e('Auto reservation', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Make reservation at moment user select date and time!', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="pre.reservation" name="pre.reservation"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'pre.reservation'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Turn nonce off', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('if you have issues with validation code that is expired in form you can turn off nonce but you are doing that on your own risk.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="nonce.off" name="nonce.off"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'nonce.off'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Default status', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Default status of Appointment made by visitor.', 'range-reserver'); ?>"></span>
                        </div>
                        <select id="rr-select-status" class="field" name="rr-select-status" data-key="default.status">
                            <option value="pending"
                            <% if (_.findWhere(settings, {rr_key:'default.status'}).rr_value ==
                            "pending") {
                            %>selected="selected"<% } %>><%= rrData.Status.pending %></option>
                            <option value="confirmed"
                            <% if (_.findWhere(settings, {rr_key:'default.status'}).rr_value ==
                            "confirmed") {
                            %>selected="selected"<% } %>><%= rrData.Status.confirmed %></option>
                            <option value="reservation"
                            <% if (_.findWhere(settings, {rr_key:'default.status'}).rr_value ==
                            "reservation") {
                            %>selected="selected"<% } %>><%= rrData.Status.reservation %></option>
                        </select>
                        <div id="rr-select-status-notification" style="display: none"><?php _e('Reservation status is short term, if you don\'t change it within 5 minutes it will be set to cancelled' , 'range-reserver');?></div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Compress shortcode output (removes new lines from templates).', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('WordPress can add auto paragraph html element for each line break. This option prevents WP from doing that on EA shortcode.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="shortcode.compress"
                                   name="shortcode.compress" type="checkbox" <% if
                            (_.findWhere(settings, {rr_key:'shortcode.compress'}).rr_value == "1") {
                            %>checked<% } %>>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-mail" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Notifications', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('You can use this tags inside email content. Just place for example #id# inside mail template and that value will be replaced with value.', 'range-reserver'); ?>"></span>
                        </div>
                        <table class='notifications form-table'>
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <p>
                                        <a class="mail-tab selected"
                                           data-textarea="#mail-pending"><?php _e('Pending', 'range-reserver'); ?></a>
                                        <a class="mail-tab"
                                           data-textarea="#mail-reservation"><?php _e('Reservation', 'range-reserver'); ?></a>
                                        <a class="mail-tab"
                                           data-textarea="#mail-canceled"><?php _e('Cancelled', 'range-reserver'); ?></a>
                                        <a class="mail-tab"
                                           data-textarea="#mail-confirmed"><?php _e('Confirmed', 'range-reserver'); ?></a>
                                        <a class="mail-tab"
                                           data-textarea="#mail-admin"><?php _e('Admin', 'range-reserver'); ?></a>
                                    </p>
                                    <textarea id="mail-template" style="height: 150px;"
                                              name="mail-template"><%= _.findWhere(settings, {rr_key:'mail.pending'}).rr_value %></textarea>
                                </td>
                            </tr>
                            <tr style="display:none;">
                                <td>
                                    <textarea id="mail-pending" class="field"
                                              data-key="mail.pending"><%= _.findWhere(settings, {rr_key:'mail.pending'}).rr_value %></textarea>
                                </td>
                                <td>
                                    <textarea id="mail-reservation" class="field"
                                              data-key="mail.reservation"><%= _.findWhere(settings, {rr_key:'mail.reservation'}).rr_value %></textarea>
                                </td>
                            </tr>
                            <tr style="display:none;">
                                <td>
                                    <textarea id="mail-canceled" class="field"
                                              data-key="mail.canceled"><%= _.findWhere(settings, {rr_key:'mail.canceled'}).rr_value %></textarea>
                                </td>
                                <td>
                                    <textarea id="mail-confirmed" class="field"
                                              data-key="mail.confirmed"><%= _.findWhere(settings, {rr_key:'mail.confirmed'}).rr_value %></textarea>
                                </td>
                            </tr>
                            <tr style="display:none;">
                                <td colspan="2">
                                    <textarea id="mail-admin" class="field" data-key="mail.admin"><%= (_.findWhere(settings, {rr_key:'mail.admin'}) != null) ? _.findWhere(settings, {rr_key:'mail.admin'}).rr_value: '' %></textarea>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div><small><?php _e('Available tags', 'range-reserver'); ?>: #id#, #date#, #start#, #end#, #status#, #created#, #price#, #ip#, #link_confirm#, #link_cancel#, #url_confirm#, #url_cancel#, #bay_name#, #bay_duration#, #bay_price#, #lane_name#,  #location_name#, #location_address#, #location_location#, <?php echo implode(', ', RRDBModels::get_custom_fields_tags()); ?></small></div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="mail.action.two_step"><?php _e('Two step action links in email', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Sometimes Mail servers can open links from email for inspection. That will trigger actions such as #link_confirm#, #link_cancel#. Mark this option if you want to have additional prompt for user action via links.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="mail.action.two_step" name="mail.action.two_step"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'mail.action.two_step'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Pending notification emails', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Enter email adress that will receive new reservation notification. Separate multiple emails with , (comma)', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="pending.email" name="pending.email"
                               type="text"
                               value="<%= _.findWhere(settings, {rr_key:'pending.email'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Admin notification subject', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('You can use any tag that is available as in custom email notifications.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="pending.subject.email"
                               name="pending.subject.email" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'pending.subject.email'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Visitor notification subject', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('You can use any tag that is available as in custom email notifications.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="pending.subject.visitor.email"
                               name="pending.subject.visitor.email" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'pending.subject.visitor.email'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="send.lane.email"><?php _e('Send email to lane', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Mark this option if you want to lane receive admin email after filing the form.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="send.lane.email"
                                   name="send.lane.email" type="checkbox" <% if
                            (_.findWhere(settings, {rr_key:'send.lane.email'}).rr_value == "1") {
                            %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="send.user.email"><?php _e('Send email to user', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Mark this option if you want to user receive email after filing the form.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="send.user.email" name="send.user.email"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'send.user.email'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Send from', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Send from email adress (Example: Name &lt;name@domain.com&gt;). Leave blank to use default address.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="send.from.email" name="send.from.email"
                               type="text"
                               value="<%- _.findWhere(settings, {rr_key:'send.from.email'}).rr_value %>">
                    </div>
                </div>
            </div>

            <div id="tab-full-calendar" class="form-section hidden">
              <span class="separator vertical"></span>
              <div class="form-container">
                  <div class="form-item">
                      <div class="label-with-tooltip">
                          <label for=""><?php _e('Allow public access to FullCalendar shortcode', 'range-reserver'); ?></label>
                          <span class="tooltip tooltip-right"
                                data-tooltip="<?php _e('By default only logged in users can see data in FullCalendar. Mark this option if you want to allow public access for all.', 'range-reserver'); ?>"></span>
                      </div>
                      <div class="field-wrap">
                          <input class="field" data-key="fullcalendar.public"
                                 name="fullcalendar.public" type="checkbox" <% if
                          (_.findWhere(settings, {rr_key:'fullcalendar.public'}).rr_value == "1") {
                          %>checked<% } %>>
                      </div>
                  </div>
                  <div class="form-item">
                      <div class="label-with-tooltip">
                          <label for=""><?php _e('Show event content in popup', 'range-reserver'); ?></label>
                          <span class="tooltip tooltip-right"
                                data-tooltip="<?php _e('Popup dialog for event content.', 'range-reserver'); ?>"></span>
                      </div>
                      <div class="field-wrap">
                          <input class="field" data-key="fullcalendar.event.show"
                                 name="fullcalendar.event.show" type="checkbox" <% if
                          (_.findWhere(settings, {rr_key:'fullcalendar.event.show'}).rr_value == "1") {
                          %>checked<% } %>>
                      </div>
                  </div>
                  <div class="form-item">
                      <div class="label-with-tooltip">
                          <label for=""><?php _e('Event content in popup', 'range-reserver'); ?></label>
                          <span class="tooltip tooltip-right"
                                data-tooltip="<?php _e('Event content when clicked on event', 'range-reserver'); ?>"></span>
                      </div>
                      <textarea id="fullcalendar-event-template" class="field" name="fullcalendar.event.template" data-key="fullcalendar.event.template"><%- (_.findWhere(settings, {rr_key:'fullcalendar.event.template'})).rr_value %></textarea>
                      <small><?php _e('Example', 'range-reserver'); ?> : (<a href="https://range-reserver.net/documentation/templates/" target="_blank"><?php _e('Full documentation', 'range-reserver');?></a>)</small>
                      <div style="display: inline-block"><code>{= event.location_name}</code><small> / </small><code>{= language}</code><small> / </small><code>{= link_confirm}</code></div>
                      <small><?php _e('To get all available options use', 'range-reserver'); ?> :</small>
                      <code>{= __CONTEXT__ | raw}</code>
                  </div>
              </div>
            </div>

            <div id="tab-labels" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <label for=""><?php _e('Bay', 'range-reserver'); ?></label>
                        <input class="field" data-key="trans.bay" name="bay" type="text"
                               value="<%= _.escape( _.findWhere(settings, {rr_key:'trans.bay'}).rr_value ) %>">
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Location', 'range-reserver'); ?></label>
                        <input class="field" data-key="trans.location" name="location" type="text"
                               value="<%= _.escape( _.findWhere(settings, {rr_key:'trans.location'}).rr_value ) %>">
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Lane', 'range-reserver'); ?></label>
                        <input class="field" data-key="trans.lane" name="lane" type="text"
                               value="<%= _.escape( _.findWhere(settings, {rr_key:'trans.lane'}).rr_value ) %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Done message', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Message that user receive after completing appointment', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="trans.done_message" name="done_message"
                               type="text"
                               value="<%= _.escape( _.findWhere(settings, {rr_key:'trans.done_message'}).rr_value ) %>">
                    </div>
                </div>
            </div>

            <div id="tab-date-time" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Time format', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Notice : date/time formating for email notification are done by Settings > General.', 'range-reserver', 'range-reserver'); ?>"></span>
                        </div>
                        <select data-key="time_format" class="field" name="time_format">
                            <option value="00-24"
                            <% if (_.findWhere(settings, {rr_key:'time_format'}).rr_value ===
                            "00-24") {
                            %>selected="selected"<% } %>>00-24</option>
                            <option value="am-pm"
                            <% if (_.findWhere(settings, {rr_key:'time_format'}).rr_value ===
                            "am-pm") {
                            %>selected="selected"<% } %>>AM-PM</option>
                        </select>
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Calendar localization', 'range-reserver'); ?></label>
                        <select data-key="datepicker" class="field" name="datepicker">
                            <% var langs = [
                            'af','ar','ar-DZ','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en','en-AU','en-GB','en-NZ','en-US','eo','es','et','eu','fa','fi','fo','fr','fr-CA','fr-CH','gl','he','hi','hr','hu','hy','id','is','it','it-CH','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl','nl-BE','nn','no','pl','pt','pt-BR','rm','ro','ru','sk','sl','sq','sr','sr-SR','sv','ta','th','tj','tr','uk','vi','zh-CN','zh-HK','zh-TW'
                            ];
                            _.each(langs,function(item,key,list){
                            if(_.findWhere(settings, {rr_key:'datepicker'}).rr_value === item) { %>
                            <option value="<%- item %>" selected="selected"><%- item %></option>
                            <% } else { %>
                            <option value="<%- item %>"><%- item %></option>
                            <% }
                            });%>
                        </select>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Block time', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('(in minutes). Prevent visitor from making an appointment if there are less minutes than this.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="block.time" name="block.time" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'block.time'}).rr_value %>">
                    </div>
                </div>
            </div>

            <div id="tab-fields" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <span class="pure-text">Create all fields that you need. Custom order them by drag and drop.</span>
                    </div>
                    <div class="form-item inline-fields">
                        <div class="form-item">
                            <label for="">Name</label>
                            <input type="text">
                        </div>
                        <div class="form-item">
                            <label for="">Type</label>
                            <select>
                                <option value="INPUT"><?php _e('Input', 'range-reserver'); ?></option>
                                <option value="MASKED"><?php _e('Masked Input', 'range-reserver'); ?></option>
                                <option value="SELECT"><?php _e('Select', 'range-reserver'); ?></option>
                                <option value="TEXTAREA"><?php _e('Textarea', 'range-reserver'); ?></option>
                                <option value="PHONE"><?php _e('Phone', 'range-reserver'); ?></option>
                                <option value="EMAIL"><?php _e('Email', 'range-reserver'); ?></option>
                            </select>
                        </div>
                        <button class="button button-primary btn-add-field button-field"><?php _e('Add', 'range-reserver'); ?></button>
                    </div>
                    <div class="form-item">
                        <ul id="custom-fields"></ul>
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* To use using the email notification for user there must be field named "email" or "e-mail" or field with type "email"', 'range-reserver'); ?></span>
                    </div>
                </div>
            </div>

            <div id="tab-captcha" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <label for=""><?php _e('Site key', 'range-reserver'); ?></label>
                        <input style="width: 100%" class="field" data-key="captcha.site-key"
                               name="captcha.site-key" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'captcha.site-key'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* Google reCAPTCHA key can be generated via', 'range-reserver'); ?> <a
                                    href="https://www.google.com/recaptcha/admin" target="_blank">LINK</a></span>
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Secret key', 'range-reserver'); ?></label>
                        <input style="width: 100%" class="field" data-key="captcha.secret-key"
                               name="captcha.secret-key" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'captcha.secret-key'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* If you want to use Captcha you must have auto reservation option turned off. If you don\'t want to use Captcha just leave fields empty.', 'range-reserver'); ?></span>
                    </div>
                </div>
            </div>

            <div id="tab-captcha-3" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <label for=""><?php _e('Site key', 'range-reserver'); ?></label>
                        <input style="width: 100%" class="field" data-key="captcha3.site-key"
                               name="captcha3.site-key" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'captcha3.site-key'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* Google reCAPTCHA key can be generated via', 'range-reserver'); ?> <a
                                    href="https://www.google.com/recaptcha/admin" target="_blank">LINK</a></span>
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Secret key', 'range-reserver'); ?></label>
                        <input style="width: 100%" class="field" data-key="captcha3.secret-key"
                               name="captcha3.secret-key" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'captcha3.secret-key'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* If you want to use Captcha you must have auto reservation option turned off. If you don\'t want to use Captcha just leave fields empty.', 'range-reserver'); ?></span>
                    </div>
                    <div class="form-item">
                        <span class="pure-text hint"><?php _e('* Only request with recaptcha score 0.5 or greater will be processed. Others will be rejected as bot calls.', 'range-reserver'); ?></span>
                    </div>
                </div>
            </div>

            <div id="tab-form" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Custom style', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Place here custom css styles. This will be included in both standard and bootstrap widget.', 'range-reserver'); ?>"></span>
                        </div>
                        <textarea class="field" data-key="custom.css"><% if (typeof _.findWhere(settings, {rr_key:'custom.css'}) !== 'undefined') { %><%- (_.findWhere(settings, {rr_key:'custom.css'})).rr_value %><% } %></textarea>
                    </div>
                    <div class="form-item">
                        <label for="send.lane.email"><?php _e('Turn off css files', 'range-reserver'); ?></label>
                        <div class="field-wrap">
                            <input class="field" data-key="css.off" name="css.off" type="checkbox"
                            <% if (_.findWhere(settings,
                            {rr_key:'css.off'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="form.label.above"><?php _e('Form label style', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Show labels above or inline with fields option on [rr_bootstrap] shortcode.', 'range-reserver'); ?>"></span>
                        </div>
                        <div>
                            <img data-value="0" class="form-label-option" title="inline" src="<?php echo plugin_dir_url( __DIR__ ) . '../img/label-inline.png';?>"/>
                            <img data-value="1" class="form-label-option" title="above" src="<?php echo plugin_dir_url( __DIR__ ) . '../img/label-above.png';?>"/>
                            <input class="field" type="hidden" name="form.label.above"
                                   data-key="form.label.above" value="<%- _.findWhere(settings,
                            {rr_key:'form.label.above'}).rr_value %>" />
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="label.from_to"><?php _e('Select label style', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Show From or From-To label on time slot in [rr_bootstrap] shortcode.', 'range-reserver'); ?>"></span>
                        </div>
                        <div>
                            <img data-value="1" class="select-label-option" title="From - To" width="200px" src="<?php echo plugin_dir_url( __DIR__ ) . '../img/label-from-to.png';?>"/>
                            <img data-value="0" class="select-label-option" title="From" width="200px" src="<?php echo plugin_dir_url( __DIR__ ) . '../img/label-from.png';?>"/>
                            <input class="field" type="hidden" name="label.from_to"
                                   data-key="label.from_to" value="<%- _.findWhere(settings,
                            {rr_key:'label.from_to'}).rr_value %>" />
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="send.lane.email"><?php _e('I agree field', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('I agree option at the end of form. If this is marked user must confirm "I agree" checkbox.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" type="checkbox" name="show.iagree"
                                   data-key="show.iagree"<% if (typeof _.findWhere(settings,
                            {rr_key:'show.iagree'}) !== 'undefined' && _.findWhere(settings,
                            {rr_key:'show.iagree'}).rr_value == '1') { %>checked<% } %> />
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Go to page', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('After a visitor creates an appointment on the front-end form. Leave blank to turn off redirect.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="submit.redirect" name="submit.redirect"
                               type="text"
                               value="<%- _.findWhere(settings, {rr_key:'submit.redirect'}).rr_value %>">
                    </div>
                    <div class="form-item subgroup">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Advance Go to', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Add custom redirect based on bay.', 'range-reserver'); ?>"></span>
                        </div>
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Bay', 'range-reserver'); ?></label>
                        <select id="redirect-bay" class="field">
                            <% _.each(rrData.Bays,function(item,key,list){ %>
                            <option value="<%= _.escape(item.id) %>"><%= _.escape(item.name) %></option>
                            <% });%>
                        </select>
                    </div>
                    <div class="form-item inline-fields">
                        <div class="form-item">
                            <label for=""><?php _e('Redirect to', 'range-reserver'); ?></label>
                            <input id="redirect-url" name="redirect-url" type="text">
                        </div>
                        <button class="button button-primary btn-add-redirect button-field"><?php _e('Add advance redirect', 'range-reserver'); ?></button>
                    </div>
                    <input type="hidden" id="advance-redirect" data-key="advance.redirect" class="field" name="advance.redirect" value="<%= _.escape(rr_settings['advance.redirect']) %>">
                    <div class="form-item">
                        <ul id="custom-redirect-list" class="list-form-item"></ul>
                    </div>
                    <hr>
                    <div class="form-item">
                        <label for=""><?php _e('After cancel go to', 'range-reserver'); ?></label>
                        <select data-key="cancel.scroll" class="field" name="cancel.scroll">
                            <% var langs = [
                            'calendar', 'lane', 'bay', 'location'
                            ];
                            _.each(langs,function(item,key,list){
                            if(typeof _.findWhere(settings, {rr_key:'cancel.scroll'}) !==
                            'undefined' &&
                            _.findWhere(settings, {rr_key:'cancel.scroll'}).rr_value === item) { %>
                            <option value="<%- item %>" selected="selected"><%- item %></option>
                            <% } else { %>
                            <option value="<%- item %>"><%- item %></option>
                            <% }
                            });%>
                        </select>
                    </div>
                    <div class="form-item subgroup">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Advance Go to on Cancel', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Add custom cancels redirect based on bay.', 'range-reserver'); ?>"></span>
                        </div>
                    </div>
                    <div class="form-item">
                        <label for=""><?php _e('Bay', 'range-reserver'); ?></label>
                        <select id="cancel-redirect-bay" class="field">
                            <% _.each(rrData.Bays,function(item,key,list){ %>
                            <option value="<%= _.escape(item.id) %>"><%= _.escape(item.name) %></option>
                            <% });%>
                        </select>
                    </div>
                    <div class="form-item inline-fields">
                        <div class="form-item">
                            <label for=""><?php _e('Redirect to', 'range-reserver'); ?></label>
                            <input id="cancel-redirect-url" name="cancel-redirect-url" type="text">
                        </div>
                        <button class="button button-primary btn-add-cancel-redirect button-field"><?php _e('Add advance redirect', 'range-reserver'); ?></button>
                    </div>
                    <div class="form-item">
                        <ul id="custom-cancel-redirect-list" class="list-form-item"></ul>
                    </div>
                    <input type="hidden" id="advance-cancel-redirect" data-key="advance_cancel.redirect" class="field" name="advance_cancel.redirect" value="<%= _.escape(rr_settings['advance_cancel.redirect']) %>">
                </div>
            </div>

            <div id="tab-gdpr" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="send.lane.email"><?php _e('Turn on checkbox', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('GDPR section checkbox.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" type="checkbox" name="gdpr.on" data-key="gdpr.on"<%
                            if (typeof _.findWhere(settings, {rr_key:'gdpr.on'}) !== 'undefined' &&
                            _.findWhere(settings, {rr_key:'gdpr.on'}).rr_value == '1') { %>checked<%
                            } %> />
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Label', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Label next to checkbox.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="gdpr.label" name="gdpr.label" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'gdpr.label'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Page with GDPR content', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Link to page with GDPR content.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="gdpr.link" name="gdpr.link" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'gdpr.link'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Error message', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Message if user don\'t mark the GDPR checkbox.', 'range-reserver'); ?>"></span>
                        </div>
                        <input class="field" data-key="gdpr.message" name="gdpr.message" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'gdpr.message'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for=""><?php _e('Clear customer data older then 6 months', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('This action will remove custom form field values older then 6 months. After that appointments older then 6 months will not hold any customer related data.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap button">
                            <input class="field" type="checkbox" name="gdpr.auto_remove" style="margin-right: 10px;" data-key="gdpr.auto_remove"<%
                            if (typeof _.findWhere(settings, {rr_key:'gdpr.auto_remove'}) !== 'undefined' &&
                            _.findWhere(settings, {rr_key:'gdpr.auto_remove'}).rr_value == '1') { %>checked<%
                            } %> /> <?php _e('Auto remove data via Cron that runs once a day','range-reserver');?><button class="button button-primary btn-gdpr-delete-data button-field" style="margin-left: 10px"><?php _e('Remove data now', 'range-reserver'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-money" class="form-section hidden">
                <span class="separator vertical"></span>
                <div class="form-container">
                    <div class="form-item">
                        <label for=""><?php _e('Currency', 'range-reserver'); ?></label>
                        <input class="field" data-key="trans.currency" name="currency" type="text"
                               value="<%- _.findWhere(settings, {rr_key:'trans.currency'}).rr_value %>">
                    </div>
                    <div class="form-item">
                        <label for="currency.before"><?php _e('Currency before price', 'range-reserver'); ?></label>
                        <div class="field-wrap">
                            <input class="field" data-key="currency.before" name="currency.before"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'currency.before'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <label for="price.hide.bay"><?php _e('Hide price in bay select', 'range-reserver'); ?></label>
                        <div class="field-wrap">
                            <input class="field" data-key="price.hide.bay" name="price.hide.bay"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'price.hide.bay'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                    <div class="form-item">
                        <div class="label-with-tooltip">
                            <label for="price.hide"><?php _e('Hide price', 'range-reserver'); ?></label>
                            <span class="tooltip tooltip-right"
                                  data-tooltip="<?php _e('Hide price in whole customers form.', 'range-reserver'); ?>"></span>
                        </div>
                        <div class="field-wrap">
                            <input class="field" data-key="price.hide" name="price.hide"
                                   type="checkbox" <% if (_.findWhere(settings,
                            {rr_key:'price.hide'}).rr_value == "1") { %>checked<% } %>>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tab-woocommerce" class="form-section hidden">
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
                    <p>Connect Bays with Products that will be added to chart after creating Appointments. You can set redirect
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
                            <td><input data-service="<%= i.id %>" data-bay="<%= i.id %>" value="" class="woo-product" type="text"></td>
                        </tr>
                        <% }); %>
                        </tbody>
                    </table>
                    <div>
                        <hr class="divider">
                    </div>
                </div>
            </div>
        </div>

        <br><br>
    </div>
</script>



<script type="text/template" id="rr-tpl-custom-forms">
    <li data-name="<%= _.escape(item.label) %>" style="display: list-item;">
        <div class="menu-item-bar">
            <div class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><%= _.escape(item.label) %></span> <span
                            class="is-submenu" style="display: none;">sub item</span></span>
                <span class="item-controls">
                <span class="item-type"><%= item.type %></span>
                    <a class="single-field-options"><i class="fa fa-chevron-down"></i></a>
                </span>
            </div>
        </div>
    </li>
</script>

<script type="text/template" id="rr-tpl-custom-form-options">
<div class="field-settings">
    <% if (item.slug && item.slug.length > 0) { %>
    <p><label>Slug :</label>
        <input type="text" class="field-slug" name="field-slug"
               value="<%- item.slug %>">
    </p>
    <% } %>
    <p>
        <label>Label</label><input type="text" class="field-label" name="field-label"
                                     value="<%= _.escape(item.label) %>">
    </p>

    <% if (item.type !== "PHONE" && item.type !== "SELECT" && item.type !== "MASKED") { %>
    <p>
        <label>Placeholder</label><input type="text" class="field-mixed" name="field-mixed"
                                           value="<%= _.escape(item.mixed) %>">
    </p>
    <% } %>

    <% if (item.type !== "PHONE" && item.type !== "SELECT" && item.type !== "MASKED") { %>
    <p>
        <label>Default value</label><input type="text" class="field-default_value" name="field-default_value"
                                         value="<%- item.default_value %>">
        <small>You can put values from logged in user (list of keys: <?php echo RRUserFieldMapper::all_field_keys(); ?>)</small>
    </p>
    <% } %>

    <% if (item.type === "PHONE") { %>
    <p>
        <label>Default value</label><select class="field-default_value" name="field-default_value"><?php require __DIR__ . '/phone.list.tpl.php';?></select>
    </p>
    <% } %>

    <% if (item.type === "MASKED") { %>
    <p>
        <label>Mask</label><input type="text" class="field-default_value" name="field-default_value" value="<%- item.default_value %>">
        <p><?php _e('Mask options', 'range-reserver');?> : </p>
        <code>9 : numeric</code> , <code>a : alphabetical</code> , <code>* : alphanumeric</code>
        <p><?php _e('Example', 'range-reserver');?> : </p>
        <code>(99) 9999[9]-9999</code> , <code>999-999-9999</code> , <code>aa-9{1,4}</code>
    </p>
    <% } %>

    <% if (item.type === "SELECT") { %>
    <p>
        <label>Options :</label>
    </p>
    <p>
    <ul class="select-options">
        <% _.each(item.options, function(element) { %>
        <li data-element="<%- element %>"><%= element %><a href="#" class="remove-select-option"><i
                        class="fa fa-trash-o"></i></a></li>
        <% }); %>
    </ul>
    </p>
    <p><input type="text"><a href="#" class="add-select-option">&nbsp;&nbsp;<i class="fa fa-plus"></i> Add option</a>
    </p>
    <% } %>
    <p>
        <label>Required :</label><input type="checkbox" class="required" name="required" <% if (item.required == "1") {
        %>checked<% } %>>
    </p>
    <p>
        <label>Visible: </label>
        <select class="visible" name="visible">
            <option value="0"
            <% if (item.visible === "0") {
            %>selected="selected"<% } %>>No</option>
            <option value="1"
            <% if (item.visible === "1") {
            %>selected="selected"<% } %>>Yes</option>
            <option value="2"
            <% if (item.visible === "2") {
            %>selected="selected"<% } %>>No, but rendered as hidden field</option>
        </select>
    </p>
    <p><a href="#" class="deletion item-delete">Delete</a> | <a href="#" class="item-save">Apply</a></p>
</div>
</script>

<script type="text/template" id="rr-tpl-advance-redirect">
    <div style="min-height: 380px; max-height: 380px;">

    </div>
    <div class="bulk-footer">
        <button id="close-advance-redirect" class="button-primary" disabled>Close</button>
    </div>
</script>

<script type="text/template" id="rr-tpl-single-advance-redirect">
    <li>
        <span class="bulk-value"><%= _.escape( _.findWhere(locations, {id:row.location}).name ) %></span>
        <span class="bulk-value"><%= _.escape( _.findWhere(bays,  {id:row.bay}).name ) %></span>
        <span class="bulk-value"><%= _.escape( _.findWhere(lanes,   {id:row.lane}).name ) %></span>
        <span style="display: inline-block;"><button class="button bulk-schedule-remove">Remove</button></span>
    </li>
</script>
