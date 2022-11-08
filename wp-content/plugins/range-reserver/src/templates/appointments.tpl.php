<script type="text/template" id="rr-appointments-main">
<?php 
	get_current_screen()->render_screen_meta();
?>
	<div class="wrap">
		<h2><?php _e('Reservations', 'range-reserver');?></h2>
		<br>
		<table id="rr-appointments-table-filter" class="filter-part wp-filter rr-responsive-table">
			<tbody>
				<tr>
					<td class="filter-label"><label for="rr-filter-locations"><strong><?php _e('Location', 'range-reserver');?> :</strong></label></td>
					<td class="filter-select">
						<select name="rr-filter-locations" id="rr-filter-locations" data-c="location">
							<option value="">-</option>
							<% _.each(cache.Locations,function(item,key,list){ %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="rr-filter-bays"><strong><?php _e('Bay', 'range-reserver');?> :</strong></label></td>
					<td class="filter-select">
						<select name="rr-filter-bays" id="rr-filter-bays" data-c="bay">
							<option value="">-</option>
							<% _.each(cache.Bays,function(item,key,list){ %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="rr-filter-lanes"><strong><?php _e('Lane', 'range-reserver');?> :</strong></label></td>
					<td class="filter-select">
						<select name="rr-filter-lanes" id="rr-filter-lanes" data-c="lane">
							<option value="">-</option>
							<% _.each(cache.Lanes,function(item,key,list){ %>
							<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label">
                        <label for="rr-filter-search"><strong><?php _e('Search', 'range-reserver');?> :</strong></label>
                        <input type="text" name="rr-filter-search" id="rr-filter-search" data-c="search">
                        <button>&#128269;</button>
                    </td>
                    <td>
                    </td>
				</tr>
				<tr>
					<td class="filter-label"><label for="rr-filter-status"><strong><?php _e('Status', 'range-reserver');?> :</strong></label></td>
					<td class="filter-select">
						<select name="rr-filter-status" id="rr-filter-status" data-c="status">
							<option value="">-</option>
							<% _.each(cache.Status,function(item,key,list){ %>
								<option value="<%= key %>"><%= item %></option>
							<% });%>
						</select>
					</td>
					<td class="filter-label"><label for="rr-filter-from"><strong><?php _e('From', 'range-reserver');?> :</strong></label></td>
					<td><input class="date-input" type="text" name="rr-filter-from" id="rr-filter-from" data-c="from"></td>
					<td class="filter-label"><label for="rr-filter-to"><strong><?php _e('To', 'range-reserver');?> :</strong></label></td>
					<td><input class="date-input" type="text" name="rr-filter-to" id="rr-filter-to" data-c="to"></td>
					<td class="filter-label"><strong><?php _e('Quick time filter', 'range-reserver');?>:</strong>
						<select id="rr-period">
                            <option value=""><?php _e('Select period', 'range-reserver');?></option>
                            <option value="today"><?php _e('Today', 'range-reserver');?></option>
                            <option value="tomorrow"><?php _e('Tomorrow', 'range-reserver');?></option>
                            <option value="7d"><?php _e('Next 7 days', 'range-reserver');?></option>
                            <option value="30d"><?php _e('Next 30 days', 'range-reserver');?></option>
                            <option value="week"><?php _e('This week', 'range-reserver');?></option>
                            <option value="month"><?php _e('This month', 'range-reserver');?></option>
						</select>
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>
		<div>
			<a href="#" class="add-new-h2 add-new">
				<i class="fa fa-plus"></i>
				<?php _e('Add New Appointment', 'range-reserver');?>
			</a>
			<a href="#" class="add-new-h2 refresh-list">
				<i class="fa fa-refresh"></i>
				<?php _e('Refresh', 'range-reserver');?>
			</a>
            <div class="rr-sort-fields">
                <label><?php _e('Sort By');?>:</label>
                <select id="rr-sort-by" name="rr-sort-by">
                    <option value="id"><?php _e('Id', 'range-reserver');?></option>
                    <option value="date"><?php _e('Date & time', 'range-reserver');?></option>
                    <option value="created"><?php _e('Created', 'range-reserver');?></option>
                </select>
                <label><?php _e('Order by');?>:</label>
                <select id="rr-order-by" name="rr-order-by">
                    <option value="ASC">asc</option>
                    <option value="DESC" selected>desc</option>
                </select>
            </div>
			<span id="status-msg" class="status"></span>
		</div>

		<table class="rr-responsive-table widefat fixed">
			<thead>
				<tr>
                    <th colspan="2" class="manage-column column-title"><a class="rr-set-sort" data-key="id" href="#">Id</a> / <?php _e('Location', 'range-reserver');?> / <?php _e('Bay', 'range-reserver');?> / <?php _e('Lane', 'range-reserver');?></th>
					<th colspan="2" class="manage-column column-title"><?php _e('Customer', 'range-reserver');?></th>
					<th class="manage-column column-title"><?php _e('Description', 'range-reserver');?></th>
					<th class="manage-column column-title"><a class="rr-set-sort" data-key="date" href="#"><?php _e('Date & time', 'range-reserver');?></a></th>
                    <th class="manage-column column-title"><?php _e('Status', 'range-reserver');?> / <?php _e('Price', 'range-reserver');?> / <a href="#" class="rr-set-sort" data-key="created"><?php _e('Created', 'range-reserver');?></a></th>
					<th class="manage-column column-title"><?php _e('Action', 'range-reserver');?></th>
				</tr>
			</thead>
			<tbody id="rr-appointments">
			</tbody>
		</table>
	</div>
</script>

<script type="text/template" id="rr-tpl-appointment-row">
	<td colspan="2" class="post-title page-title column-title">
		<strong>#<%= row.id %></strong>
		<strong><%= _.escape( _.findWhere(cache.Locations, {id:row.location}).name ) %></strong>
		<strong><%= _.escape( _.findWhere(cache.Bays, {id:row.bay}).name ) %></strong>
		<strong><%= _.escape( _.findWhere(cache.Lanes, {id:row.lane}).name ) %></strong>
	</td>
	<td colspan="2">
		<% _.each(cache.MetaFields,function(item,key,list) { %>
			<% if (row[item.slug] !== "undefined" && item.type !== 'TEXTAREA') { %>
			<strong><%= _.escape(row[item.slug]) %></strong><br>
			<% } %>
		<% });%>
	</td>
	<td>
		<% _.each(cache.MetaFields,function(item,key,list) { %>
			<% if (row[item.slug] !== "undefined" && item.type === 'TEXTAREA') { %>
			<strong><%= _.escape(row[item.slug]) %></strong><br>
			<% } %>
		<% });%>
	</td>
	<td>
		<strong><%= _.formatDate(row.date) %> - <%= _.formatTime(row.start) %></strong><br>
		<strong><%= _.formatDate(row.end_date) %> - <%= _.formatTime(row.end) %></strong>
	</td>
	<td>
		<strong><%= rrData.Status[row.status] %></strong><br>
		<!-- <strong><%= row.user %></strong><br> -->
		<strong><%= row.price %></strong><br>
		<strong><%= _.formatDateTime(row.created) %></strong>
	</td>
	<td class="action-center">
		<button class="button btn-edit"><?php _e('Edit', 'range-reserver');?></button>
		<button class="button btn-del"><?php _e('Delete', 'range-reserver');?></button>
		<button class="button btn-clone"><?php _e('Clone', 'range-reserver');?></button>
	</td>
</script>

<script type="text/template" id="rr-tpl-appointment-row-edit">
<td colspan="8">
	<table class="inner-edit-table rr-responsive-table">
		<tbody>
			<tr>
				<td colspan="2">
					<select class="app-fields" name="rr-input-locations" id="rr-input-locations" data-prop="location">
						<option value=""> -- <?php _e('Location', 'range-reserver');?> -- </option>
						<% _.each(cache.Locations,function(item,key,list){
						if (item.id == row.location) { %>
							<option value="<%= item.id %>" selected="selected"><%= _.escape(item.name) %></option>
						<% } else { %>
							<option value="<%= item.id %>"><%= _.escape(item.name) %></option>
						<% }
						});%>
					</select><br>
					<select class="app-fields rr-bay" name="rr-input-bays" id="rr-input-bays" data-prop="bay">
						<option value=""> -- <?php _e('Bay', 'range-reserver');?> -- </option>
						<% _.each(cache.Bays,function(item,key,list){
							if (item.id == row.bay) { %>
								<option value="<%= item.id %>" data-duration="<%= item.duration %>" data-price="<%= item.price %>" selected="selected"><%= _.escape( item.name ) %></option>
						<% } else { %>
								<option value="<%= item.id %>" data-duration="<%= item.duration %>"  data-price="<%= item.price %>"><%= _.escape( item.name ) %></option>
						<% }
						});%>
					</select><br>
					<select class="app-fields" name="rr-input-lanes" id="rr-input-lanes" data-prop="lane">
						<option value=""> -- <?php _e('Lane', 'range-reserver');?> -- </option>
						<% _.each(cache.Lanes,function(item,key,list){
							if(item.id == row.lane) { %>
								<option value="<%= item.id %>" selected="selected"><%= _.escape( item.name ) %></option>
						<% } else { %>
								<option value="<%= item.id %>"><%= _.escape( item.name ) %></option>
						<% }
						});%>
					</select>
				</td>
				<td colspan="2">
					<% _.each(cache.MetaFields,function(item,key,list) { %>
						<% if(item.type === 'INPUT' || item.type === 'MASKED') { %>
						<input type="text" data-prop="<%= item.slug %>" placeholder="<%= _.escape( item.label ) %>" value="<% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %>"><br>
                        <% } else if(item.type === 'PHONE') { %>
                        <input type="text" data-prop="<%= item.slug %>" placeholder="<%= _.escape( item.label ) %>" value="<% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %>"><br>
                        <% } else if(item.type === 'EMAIL') { %>
                        <input type="text" data-prop="<%= item.slug %>" placeholder="<%= _.escape( item.label ) %>" value="<% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %>"><br>
                        <% } else if(item.type === 'SELECT') { %>
							<select data-prop="<%= item.slug %>">
								<% _.each(item.mixed.split(','),function(i,k,l) {
									if(typeof row[item.slug] !== 'undefined' && i === row[item.slug]) { %>
								%>
								<option value="<%= i %>" selected><%= _.escape( i ) %></option>
								<% } else { %>
								<option value="<%= i %>" ><%= _.escape( i ) %></option>
								<% }});%>
							</select>
						<% } %>
					<% });%>
				</td>
				<td colspan="2">
					<% _.each(cache.MetaFields,function(item,key,list) { %>
						<% if(item.type === 'TEXTAREA') { %>
						<textarea rows="3" data-prop="<%= item.slug %>" placeholder="<%= item.label %>"><% if (typeof row[item.slug] !== "undefined") { %><%= _.escape( row[item.slug] ) %><% } %></textarea><br>
						<% } %>
					<% });%>
				</td>
				<td>
					<p><?php _e('Date', 'range-reserver');?> :</p>
					<input id="date-start" class="app-fields date-start" type="text" data-prop="date" value="<%= row.date %>"><br>
					<p><?php _e('Time', 'range-reserver');?> :</p>
					<select data-prop="start" disabled="disabled" class="time-start">
					</select>
				</td>
				<td>
					<select name="rr-select-status" data-prop="status">
						<% _.each(cache.Status,function(item,key,list){
							if(key == row.status) { %>
								<option value="<%= key %>" selected="selected"><%= item %></option>
						<% } else { %>
								<option value="<%= key %>"><%= item %></option>
						<% }
						});%>
					</select>
					<span><?php _e('Price', 'range-reserver');?> : </span><input class="rr-price" style="width: 50px" type="text" data-prop="price" value="<%= row.price %>">
					<!-- <strong><%= row.user %></strong><br>
					<strong><%= row.created %></strong>-->
				</td>
			</tr>
			<tr>
				<td colspan="6">
					<label for="send-mail"> <?php _e('Send email notification :', 'range-reserver');?> </label>
					<input name="send-mail" type="checkbox" checked="checked">
				</td>
				<td colspan="2" style="text-align: right;">
					<button class="button button-primary btn-save"><?php _e('Save', 'range-reserver');?></button>
					<button class="button btn-cancel"><?php _e('Cancel', 'range-reserver');?></button>
				</td>
			</tr>
		</tbody>
	</table>
</td>
</script>

<script type="text/template" id="rr-tpl-appointment-times">
<% _.each(times,function(item,key,list){ 
	if(app.start === item.value) { %>
	<option value="<%= item.value %>" selected="selected"><%= item.show %></option>
	<% } else { %>
		<option value="<%= item.value %>" <% if(item.count < 1) {%>disabled<% } %>><%= item.show %> - <%= item.ends %></option>
	<% } %>
<% });%>
</script>