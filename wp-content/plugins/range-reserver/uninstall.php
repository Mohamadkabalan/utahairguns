<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

require_once dirname(__FILE__) . '/main.php';

\WPFront\URE\WPFront_User_Role_Editor_Uninstall::uninstall();

