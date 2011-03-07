<?php
  /**
   * Log4php sample
   *
   */
require_once(dirname(__FILE__) . '/../../vendors/log4php/Logger.php');
Logger::configure(dirname(__FILE__) . '/log4php.properties');

if (!class_exists('File')) {
    App::import('Lib', 'File');
}

class Log4php {

    private $logger;

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    function write($type, $message) {
        $this->logger = Logger::getLogger('log');
        return $this->logger->{$type}($message);
    }
}
