<?php
App::uses('CakeLogInterface', 'Log');
App::uses('FileLog', 'Log/Engine');

/**
 * File Storage stream for Logging with log rotate
 *
 */
if (!class_exists('File')) {
    App::uses('File', 'Utility');
}

class RotateFileLog extends FileLog {

    protected $_path = null;
    protected $_prefix = 'error';
    protected $_suffix = '';
    protected $_rotate = null;

    const STORAGE_S3 = 'S3';

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    public function write($type, $message) {
        if (!$this->_checkLogOutputLevel($type)) {
            return ;
        }

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

        // @see FileLog::write()
        if (!empty($this->_file)) {
            $filename = $this->_file;
        } elseif ($type == 'error' || $type == 'warning') {
            $filename = 'error.log';
        } elseif (in_array($type, $debugTypes)) {
            $filename = 'debug.log';
        } elseif (isset($this->_config) && in_array($type, $this->_config['scopes'])) { // 2.1.x compatible
            $filename = $this->_file;
        } else {
            $filename = $type . '.log';
        }

        $this->_prefix = preg_replace('/\.([^\.]+)$/', '', $filename);
        $extension = end(explode('.', $filename));

        $filename = $this->_path . $this->_prefix . '_' . $this->_suffix . (!empty($extension) ? '.' . $extension : '');
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
            $logs = glob($this->_path . $this->_prefix . '_*'. (!empty($extension) ? '.' . $extension : ''));
            while(count($logs) > $this->_rotate) {
                if (!$this->_removeLog($logs[0])) {
                    return false;
                }
                array_shift($logs);
            }
        }
        return true;
    }

    /**
     * _removeLog
     *
     * @param $filePath
     */
    private function _removeLog($filePath){
        if (Configure::read('Yalog.RotateFileLog.backup') == RotateFileLog::STORAGE_S3) {
            return $this->_moveLogS3($filePath);
        }
        $deleteLog = new File($filePath, true);
        return $deleteLog->delete();
    }

    /**
     * _moveLogS3
     *
     * @param $filePath
     */
    private function _moveLogS3($filePath){
        if (!class_exists('AmazonS3')
            || !Configure::read('Yalog.S3.key')
            || !Configure::read('Yalog.S3.secret')
            || !Configure::read('Yalog.S3.bucket')) {
            return false;
        }
        $fileName = basename($filePath);
        $options = array('key' => Configure::read('Yalog.S3.key'),
                         'secret' => Configure::read('Yalog.S3.secret'),
                         );
        $bucket = Configure::read('Yalog.S3.bucket');
        $s3 = new AmazonS3($options);
        $region = Configure::read('Yalog.S3.region');
        if (!empty($region)) {
            $s3->set_region($region);
        }
        $acl = Configure::read('Yalog.S3.acl');
        if (empty($acl)) {
            $acl = AmazonS3::ACL_PRIVATE;
        }
        $urlPrefix = Configure::read('Yalog.S3.urlPrefix');
        $responce = $s3->create_object($bucket,
                                       $urlPrefix . $fileName,
                                       array(
                                             'fileUpload' => $filePath,
                                             'acl' => $acl,
                                             ));
        if (!$responce->isOK()) {
            //__('Validation Error: S3 Upload Error');
            return false;
        }
        $deleteLog = new File($filePath, true);
        return $deleteLog->delete();
    }

    /**
     * _checkLogOutputLevel
     * check level of log output
     * Compare the number of log level and the one of output level set in bootstrap.php
     *
     * @param string $type output log level
     * @return boolean true:output, false:not to do
     */
    private function _checkLogOutputLevel($type) {
        $setLevel = Configure::read('Yalog.OutputLevel');

        // Output all log when it is NULL
        if (is_null($setLevel)) {
            return true;
        }

        // All output log is stopped when it is false.
        if ($setLevel === false) {
            return false;
        }

        // Levels converted in CakeLog::write
        $levels = array(
                        'warning' => LOG_WARNING,
                        'notice' => LOG_NOTICE,
                        'info' => LOG_INFO,
                        'debug' => LOG_DEBUG,
                        'error' => LOG_ERROR
                    );

        // Output all logs when there is not $setLevel in levels converted in CakeLog::write
        if (!is_int($setLevel) && !in_array($setLevel, $levels)) {
            return true;
        }

        if (isset($levels[$type])) {
            $level = $levels[$type];
        } elseif (is_int($type)) {
            $level = $type;
        }

        if (isset ($level) && ($level > $setLevel)) {
            return false;
        }
        return true;
    }

}
