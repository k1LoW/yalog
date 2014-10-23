<?php
App::uses('CakeLogInterface', 'Log');
App::uses('FileLog', 'Log/Engine');
require_once(dirname(__FILE__) . '/../../../vendor/autoload.php');

use Aws\S3\S3Client;
use Aws\Common\Enum\Region;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;

/**
 * File Storage stream for Logging with log rotate
 *
 */
if (!class_exists('File')) {
    App::uses('File', 'Utility');
}

class S3Log extends FileLog {

    protected $_path = null;
    protected $_prefix = 'error';
    protected $_suffix = '';
    protected $_rotate = null;
    protected $_bufferPrefix = 's3_buffer_';

    /**
     * Implements writing to log files.
     *
     * @param string $type The type of log you are making.
     * @param string $message The message you want to log.
     * @return boolean success of write.
     */
    public function write($type, $message) {
        $debugTypes = array('notice', 'info', 'debug');
        $this->_suffix = date('Ymd');
        if (Configure::read('Yalog.S3Log.monthly') == true) {
            $this->_suffix = date('Ym');
        }
        if (Configure::read('Yalog.S3Log.weekly') == true) {
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

        // Add buffer prefix
        $filename = $this->_bufferPrefix . $filename;

        $this->_prefix = preg_replace('/\.([^\.]+)$/', '', $filename);
        $exploded = explode('.', $filename);
        $extension = end($exploded);

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

        // upload to S3
        $logs = glob($this->_path . $this->_prefix . '_*'. (!empty($extension) ? '.' . $extension : ''));
        while(count($logs) > 1) {
            if (!$this->_moveLogS3($logs[0])) {
                return false;
            }
            array_shift($logs);
        }
        return true;
    }

    /**
     * _moveLogS3
     *
     * @param $filePath
     */
    private function _moveLogS3($filePath){
        if (!class_exists('Aws\S3\S3Client')
            || !Configure::read('Yalog.S3Log.key')
            || !Configure::read('Yalog.S3Log.secret')
            || !Configure::read('Yalog.S3Log.bucket')) {
            return false;
        }
        $fileName = preg_replace('/' . $this->_bufferPrefix . '/', '', basename($filePath));
        $options = array(
            'key' => Configure::read('Yalog.S3Log.key'),
            'secret' => Configure::read('Yalog.S3Log.secret'),
        );
        $bucket = Configure::read('Yalog.S3Log.bucket');
        $s3 = S3Client::factory($options);
        $region = Configure::read('Yalog.S3Log.region');
        if (!empty($region)) {
            $s3->setRegion($region);
        }
        $acl = Configure::read('Yalog.S3Log.acl');
        if (empty($acl)) {
            $acl = CannedAcl::PUBLIC_READ;
        }
        $urlPrefix = Configure::read('Yalog.S3Log.urlPrefix');

        try {
            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $urlPrefix . $fileName,
                'Body' => EntityBody::factory(fopen($filePath, 'r')),
                'ACL' => $acl,
            ));
            $deleteLog = new File($filePath, true);
            return $deleteLog->delete();
        } catch (S3Exception $e) {
            throw new S3Exception($e->getMessage());
            return false;
        }
    }
}
