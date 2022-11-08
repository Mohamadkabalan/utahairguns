<?php

class RRC_Log_Helper extends Katzgrau\KLogger\Logger
{
    public function setFileHandle($writeMode)
    {
        if (!file_exists($this->getLogFilePath())) {
            $log_content = "<?php if (!defined('WPINC') || !current_user_can('activate_plugins')) { die('Access denied!'); }  ?>\n";
            file_put_contents($this->getLogFilePath(), $log_content);
        }

        parent::setFileHandle($writeMode);
    }

}