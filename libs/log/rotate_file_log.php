<?php
  /**
   * File Storage stream for Logging with log rotate
   *
   */
if (!class_exists('File')) {
    require LIBS . 'file.php';
}

class RotateFileLog {

    var $_path = null;
    var $_prefix = 'error';
    var $_suffix = '';
    var $_rotate = null;

    function RotateFileLog($options = array()) {
        $options += array('path' => LOGS);
        $this->_path = $options['path'];
    }

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    function write($type, $message) {
        $debugTypes = array('notice', 'info', 'debug');
        $this->_suffix = date('Ymd');
        if (Configure::read('Yalog.RotateFileLog.monthly') == true) {
            $this->_suffix = date('Ym');
        }
        if (Configure::read('Yalog.RotateFileLog.weekly') == true) {
            if (date('w') == 0) {
                $this->_suffix = date('Ymd') . 'w';
            } else {
                $this->_suffix = date('Ymd', strtotime('-' . date('w') . ' day')) . 'w';
            }
        }
        if ($type == 'error' || $type == 'warning') {
            $this->_prefix = 'error';
        } elseif (in_array($type, $debugTypes)) {
            $this->_prefix = 'debug';
        } else {
            $this->_prefix = $type;
        }
        $filename = $this->_path . $this->_prefix . '_' . $this->_suffix .'.log';
        $output = date('Y-m-d H:i:s') . ' ' . ucfirst($type) . ': ' . $message . "\n";
        $log = new File($filename, true);
        // Write Log
        if (!$log->writable()) {
            return false;
        }
        if (!$log->append($output)) {
            return false;
        }
        // Rotate log
        if (Configure::read('Yalog.RotateFileLog.rotate')) {
            $this->_rotate = Configure::read('Yalog.RotateFileLog.rotate');
            $logs = glob($this->_path . $this->_prefix . '_*.log');
            while(count($logs) > $this->_rotate) {
                $deleteLog = new File($logs[0], true);
                if (!$deleteLog->delete()) {
                    return false;
                }
                array_shift($logs);
            }
        }
        return true;
    }
}
