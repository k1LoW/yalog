<?php
App::uses('CakeLogInterface', 'Log');
App::uses('FileLog', 'Log/Engine');
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

/**
 * File Storage stream for Logging with log rotate
 *
 */
if (!class_exists('File')) {
    App::uses('File', 'Utility');
}

use Fluent\Logger\FluentLogger;

Fluent\Autoloader::register();

class FluentLog Implements CakeLogInterface {

    private $logger;

    /**
     * __construct
     *
     */
    public function __construct(){
        $this->logger = new FluentLogger("localhost","24224");
    }

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    public function write($type, $message) {
        if (!is_array($message)) {
            $message = array('message' => $message);
        }
        return $this->logger->post($type, $message);
    }
}
