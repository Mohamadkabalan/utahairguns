<?php

declare (strict_types=1);
namespace FedExVendor\WPDesk\Logger;

use FedExVendor\Monolog\Handler\HandlerInterface;
use FedExVendor\Monolog\Handler\NullHandler;
use FedExVendor\Monolog\Logger;
use FedExVendor\Monolog\Handler\ErrorLogHandler;
use FedExVendor\WPDesk\Logger\WC\WooCommerceHandler;
final class SimpleLoggerFactory implements \FedExVendor\WPDesk\Logger\LoggerFactory
{
    /** @var Settings */
    private $options;
    /** @var string */
    private $channel;
    /** @var Logger */
    private $logger;
    public function __construct(string $channel, \FedExVendor\WPDesk\Logger\Settings $options = null)
    {
        $this->channel = $channel;
        $this->options = $options ?? new \FedExVendor\WPDesk\Logger\Settings();
    }
    public function getLogger($name = null) : \FedExVendor\Monolog\Logger
    {
        if ($this->logger) {
            return $this->logger;
        }
        $logger = new \FedExVendor\Monolog\Logger($this->channel);
        $wc_handler = $this->get_wc_handler();
        if ($this->options->use_wc_log) {
            $logger->pushHandler($wc_handler);
        }
        if ($this->options->use_wp_log || $wc_handler instanceof \FedExVendor\Monolog\Handler\NullHandler) {
            $logger->pushHandler($this->get_wp_handler());
        }
        return $this->logger = $logger;
    }
    private function get_wc_handler() : \FedExVendor\Monolog\Handler\HandlerInterface
    {
        if (\function_exists('wc_get_logger')) {
            return new \FedExVendor\WPDesk\Logger\WC\WooCommerceHandler(\wc_get_logger(), $this->options->level);
        }
        return new \FedExVendor\Monolog\Handler\NullHandler();
    }
    private function get_wp_handler() : \FedExVendor\Monolog\Handler\HandlerInterface
    {
        if (\defined('FedExVendor\\WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            return new \FedExVendor\Monolog\Handler\ErrorLogHandler(\FedExVendor\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, $this->options->level);
        }
        return new \FedExVendor\Monolog\Handler\NullHandler();
    }
}
