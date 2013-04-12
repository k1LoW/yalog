<?php
App::uses('CakeLog', 'Log');
App::uses('S3Log', 'Yalog.Log');

class S3LogTestCase extends CakeTestCase {

    /**
     * setUp
     *
     */
    public function setUp(){
        $configs = CakeLog::configured();
        foreach ($configs as $key => $config) {
            CakeLog::drop($config);
        }
    }

    /**
     * tearDown
     *
     */
    public function tearDown(){
    }

    /**
     * testFileLog
     *
     */
    public function testFileLog(){
        CakeLog::config('test_log', array(
                'engine' => 'FileLog',
                'type' => array('test_log_type'),
                'file' => 'test_debug',
            ));
        $hash = sha1(time() . 'testFileLog');
        CakeLog::write('test_log_type', $hash);
        if (preg_match('/^2\.1\./', Configure::version())) {
            // CakePHP 2.1.x
            $logPath = LOGS . 'test_log_type.log';
        } else {
            $logPath = LOGS . 'test_debug.log';
        }

        $this->assertTrue(file_exists($logPath));
        $log = file_get_contents($logPath);
        $this->assertTrue(strpos($log, $hash) > 0);
        @unlink($logPath);
    }

    /**
     * testS3Log
     *
     */
    public function testS3Log(){
        CakeLog::config('test_s3_log', array(
                'engine' => 'Yalog.S3Log',
                'type' => array('test_s3_log_type'),
                'file' => 'test_debug',
            ));
        Configure::write('Yalog.S3Log.key', AWS_ACCESS_KEY);
        Configure::write('Yalog.S3Log.secret', AWS_SECRET_ACCESS_KEY);
        Configure::write('Yalog.S3Log.bucket', AWS_S3_BUCKET);
        Configure::write('Yalog.S3Log.region', AmazonS3::REGION_TOKYO);
        Configure::write('Yalog.S3Log.urlPrefix', 'test_logs/');

        $hash = sha1(time() . 'testS3Log');
        if (preg_match('/^2\.1\./', Configure::version())) {
            // CakePHP 2.1.x
            $prefix = 's3_buffer_' . 'test_s3_log_type_';
        } else {
            $prefix = 's3_buffer_' . 'test_debug_';
        }
        for ($i = 1; $i <= 5; $i++) {
            $logPath = LOGS . $prefix . date('Ymd', strtotime('-' . $i . 'day')) . '.log';
            file_put_contents($logPath, $hash);
        }
        $this->assertIdentical(count(glob(LOGS . $prefix . '*.log')), 5);

        CakeLog::write('test_s3_log_type', $hash);
        $logPath = LOGS . $prefix . date('Ymd') . '.log';
        $this->assertTrue(file_exists($logPath));
        $this->assertIdentical(count(glob(LOGS . $prefix . '*.log')), 1);

        $logPath = LOGS . $prefix . date('Ymd', strtotime('-5day')) . '.log';
        $this->assertFalse(file_exists($logPath));

        foreach (glob(LOGS . $prefix . '*.log') as $log) {
            @unlink($log);
        }
    }
}