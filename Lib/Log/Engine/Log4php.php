<?php
App::uses('CakeLogInterface', 'Log');

/**
 * Log4php sample
 *
 */
//require_once(dirname(__FILE__) . '/../../../Vendor/log4php/Logger.php');
App::import('Vendor', 'Yalog.log4php/Logger');
Logger::configure(dirname(__FILE__) . '/log4php.properties');

if (!class_exists('File')) {
    App::uses('File', 'Utility');
}

class Log4php implements CakeLogInterface {

    private $logger;

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    public function write($type, $message) {
        $this->logger = Logger::getLogger('log');
        return $this->logger->{$type}($message);
    }
}
