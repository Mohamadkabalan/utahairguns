<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    </head>
    <body>
        <table border="0" cellpadding="15" cellspacing="0" width="500">
            <tbody>
            <tr>
                <td style="text-align:left; background-color: #CCFFFF;"><?php _e('Id', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['id'];?></td>
            </tr>
            <tr>
                <td style="text-align:left;"><?php _e('Status', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo $data['status'];?></td>
            </tr>
            <tr>
                <td style="text-align:left; background-color: #CCFFFF;"><?php _e('Location', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['location_name'];?></td>
            </tr>
            <tr>
                <td style="text-align:left;"><?php _e('Bay', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo $data['bay_name'];?></td>
            </tr>
            <tr>
                <td style="text-align:left; background-color: #CCFFFF;"><?php _e('Lane', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['lane_name'];?></td>
            </tr>
            <tr>
                <td style="text-align:left;"><?php _e('Date', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo $data['date'];?></td>
            </tr>
            <tr>
                <td style="text-align:left; background-color: #CCFFFF;"><?php _e('Start', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['start'];?></td>
            </tr>
            <tr>
                <td style="text-align:left;"><?php _e('End', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo $data['end'];?></td>
            </tr>
            <tr>
                <td style="text-align:left; background-color: #CCFFFF;"><?php _e('Created', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['created'];?></td>
            </tr>
            <tr>
                <td style="text-align:left;"><?php _e('Price', 'range-reserver');?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo $data['price'];?></td>
            </tr>
            <tr>
                <td style="text-align: left; background-color: #CCFFFF;">IP</td>
                <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;"><?php echo $data['ip'];?></td>
            </tr>

            <?php
            $count = 1;
            foreach ($meta as $field) {
                if(array_key_exists($field->slug, $data)) {
                    if($count++ % 2 == 1) {
                        echo '<tr>
                                    <td style="text-align:left;">' . $field->label . '</td>
                                    <td style="text-align: right; font-weight: bold;">' . $data[$field->slug] . '</td>
                              </tr>';
                    } else {
                        echo '<tr>
                                    <td style="text-align:left; background-color: #CCFFFF;">' . $field->label . '</td>
                                    <td style="text-align: right; font-weight: bold; background-color: #CCFFFF;">' . $data[$field->slug] . '</td>
                              </tr>';
                    }
                }
            }
            ?>
                </tbody>
        </table>
        <p style="font-weight: bold">- #link_confirm#</p>
        <p style="font-weight: bold">- #link_cancel#</p>
    </body>
</html>