<script type="text/template" id="rr-report-main">
    <div class="report-container">
        <div id="tab-header" style="padding-top: 20px; padding-bottom: 20px">
            <div class="report-items">
                <div class="report-item time-table report-card" data-report="overview">
                    <i class="icon icon-timetable"></i>
                    <span class="rep-title"><?php _e('Time table', 'range-reserver'); ?></span>
                    <span class="rep-description"><?php _e('Have Calendar overview of all bookings and free slots. ','range-reserver'); ?></span>
                </div>
                <div class="report-item money" style="display: none;">
                    <i class="icon icon-money-2"></i>
                    <span class="rep-title"><?php _e('Money', 'range-reserver'); ?></span>
                    <span class="rep-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent sed est id ipsum elementum dapibus.</span>
                </div>
                <div class="report-item export report-card" data-report="excel">
                    <i class="icon icon-export"></i>
                    <span class="rep-title"><?php _e('Export', 'range-reserver'); ?></span>
                    <span class="rep-description"><?php _e('Export data in Excel CSV format for selected time period.', 'range-reserver'); ?></span>
                </div>
            </div>
            <div class="back-section" style="display: none;">
                <button class="button-primary go-back" style="padding-left: 10px"><span style="padding-top: 4px;" class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Back to Reports', 'range-reserver'); ?></button>
            </div>
        </div>
        <div id="report-content" class="report-content">
        </div>
    </div>
</script>

<!-- template for overview report -->
<script type="text/template" id="rr-report-overview">
    <div class="filter-select">
        <div>
            <div class="form-item">
                <label htmlFor=""><?php _e('Location', 'range-reserver'); ?> :</label>
                <select name="location" id="overview-location" class="field">
                    <option value="">-</option>
                    <% _.each(cache.Locations,function(item,key,list){ %>
                    <option value="<%= item.id %>"><%= item.name %></option>
                    <% });%>
                </select>
            </div>
            <div class="form-item">
                <label htmlFor=""><?php _e('Bay', 'range-reserver'); ?> :</label>
                <select name="bay" id="overview-bay" class="field">
                    <option value="">-</option>
                    <% _.each(cache.Bays,function(item,key,list){ %>
                    <option value="<%= item.id %>"><%= item.name %></option>
                    <% });%>
                </select>
            </div>
            <div class="form-item">
                <label htmlFor=""><?php _e('Lane', 'range-reserver'); ?> :</label>
                <select name="lane" id="overview-lane" class="field">
                    <option value="">-</option>
                    <% _.each(cache.Lanes,function(item,key,list){ %>
                    <option value="<%= item.id %>"><%= item.name %></option>
                    <% });%>
                </select>
            </div>
            <span>&nbsp&nbsp;</span>
            <button class="refresh button-primary"><?php _e('Refresh', 'range-reserver'); ?></button>
            <br><br>
        </div>
    </div>
    <div name="month" class="datepicker overview-month" id="overview-month" />
    <br>
    <div id="overview-data" class="overview-data"></div>
</script>

<!-- Template for export report -->
<script type="text/template" id="rr-report-excel">
    <div>
        <div class="custom-cols-block">
            <div class="form-item">
                <label for=""><?php _e('Fields', 'range-reserver'); ?></label>
                <div class="field">
                    <a id="rr-export-customize-columns-toggle" href="#"><?php _e('Click to customize columns for export', 'range-reserver'); ?></a>
                    <div id="rr-export-customize-columns" style="display: none;">
                        <p>Columns: <b><?php echo implode(', ', $this->models->get_all_tags_for_template()); ?></b></p>
                        <?php _e('Place fields separate by , for example: id,name,email', 'range-reserver'); ?>
                        <p><input id="rr-export-custom-columns" type="text" style="" value="<?php echo get_option('rr_excel_columns', ''); ?>"/></p>
                        <button id="rr-export-save-custom-columns" class="btn button-primary"><?php _e('Save settings', 'range-reserver'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <form id="rr-export-form" class="rr-export-form" action="<%= export_link %>" method="get">
            <input type="hidden" name="action" value="rr_export">
            <div class="form-item">
                <label for=""><?php _e('From', 'range-reserver'); ?></label>
                <input class="rr-datepicker field" type="text" name="rr-export-from" autocomplete="off">
            </div>
            <div class="form-item">
                <label for=""><?php _e('To', 'range-reserver'); ?></label>
                <input class="rr-datepicker field" type="text" name="rr-export-to" autocomplete="off">
            </div>
            <p><?php _e('Export data to CSV, can be imported to MS Excel, OpenOffice Calc... ', 'range-reserver'); ?></p>
            <button class="eadownloadcsv button-primary"><?php _e('Export data', 'range-reserver'); ?></button>
        </form>
    </div>
</script>