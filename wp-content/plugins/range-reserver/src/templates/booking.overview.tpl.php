<script type="text/template" id="rr-appointments-overview">
    <small><%= settings['trans.overview-message'] %></small>
    <table>
        <tbody>
        <% if(settings['rtl'] == '1') { %>
            <% if(data.location.indexOf('_') !== 0) { %>
            <tr class="row-location">
                <td class="rr-label"><%= _.escape( settings['trans.location'] ) %></td>
                <td class="value"><%= _.escape( data.location ) %></td>
            </tr>
            <% } %>
            <% if(data.bay.indexOf('_') !== 0) { %>
            <tr class="row-bay">
                <td class="rr-label"><%= _.escape( settings['trans.bay'] ) %></td>
                <td class="value"><%= _.escape( data.bay ) %></td>
            </tr>
            <% } %>
            <% if(data.lane.indexOf('_') !== 0) { %>
            <tr class="row-lane">
                <td class="rr-label"><%= _.escape( settings['trans.lane'] ) %></td>
                <td class="value"><%= _.escape( data.lane ) %></td>
            </tr>
            <% } %>
            <% if (settings['price.hide'] !== '1') { %>
            <tr class="row-price">
                <td class="rr-label"><%= settings['trans.price'] %></td>
                <td class="value"><%= _.escape( data.price ) %> <%= _.escape( settings['trans.currency'] ) %></td>
            </tr>
            <% } %>
            <tr class="row-datetime">
                <td class="rr-label"><%= settings['trans.date-time'] %></td>
                <td class="value"><%= data.date %> <%= data.time %></td>
            </tr>
        <% } else { %>
            <% if(data.location.indexOf('_') !== 0) { %>
            <tr class="row-location">
                <td class="rr-label"><%= _.escape( settings['trans.location'] ) %></td>
                <td class="value"><%= _.escape( data.location ) %></td>
            </tr>
            <% } %>
            <% if(data.bay.indexOf('_') !== 0) { %>
            <tr class="row-bay">
                <td class="rr-label"><%= _.escape( settings['trans.bay'] ) %></td>
                <td class="value"><%= _.escape( data.bay ) %></td>
            </tr>
            <% } %>
            <% if(data.lane.indexOf('_') !== 0) { %>
            <tr class="row-lane">
                <td class="rr-label"><%= _.escape( settings['trans.lane'] ) %></td>
                <td class="value"><%= _.escape( data.lane ) %></td>
            </tr>
            <% } %>
            <% if (settings['price.hide'] !== '1') { %>
            <tr class="row-price">
                <td class="rr-label"><%= _.escape( settings['trans.price'] ) %></td>
                <% if (settings['currency.before'] == '1') { %>
                <td class="value"><%= settings['trans.currency'] %><%= _.escape( data.price ) %></td>
                <% } else { %>
                <td class="value"><%= _.escape( data.price ) %><%= _.escape( settings['trans.currency'] ) %></td>
                <% } %>
            </tr>
            <% } %>
            <tr class="row-datetime">
                <td class="rr-label"><%= settings['trans.date-time'] %></td>
                <td class="value"><%= data.date_time %></td>
            </tr>
        <% } %>
        </tbody>
    </table>
    <div id="rr-total-amount" style="display: none;" data-total="<%= data.price %>"></div>
</script>
