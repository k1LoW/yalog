<?php
App::uses('CakeLog', 'Log');
App::uses('RotateFileLog', 'Yalog.Log');
class RotateFileLogTestCase extends CakeTestCase {

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
        if (preg_match('/^2\.2\./', Configure::version())) {
            $logPath = LOGS . 'test_debug.log';
        } else {
            // CakePHP 2.1.x
            $logPath = LOGS . 'test_log_type.log';
        }

        $this->assertTrue(file_exists($logPath));
        $log = file_get_contents($logPath);
        $this->assertTrue(strpos($log, $hash) > 0);
        @unlink($logPath);
    }

    /**
     * testRotateFileLog
     *
     */
    public function testRotateFileLog(){
        CakeLog::config('test_rotate_log', array(
                                                 'engine' => 'Yalog.RotateFileLog',
                                                 'type' => array('test_rotate_log_type'),
                                                 'file' => 'test_debug',
                                                 ));
        $hash = sha1(time() . 'testRotateFileLog');
        CakeLog::write('test_rotate_log_type', $hash);
        if (preg_match('/^2\.2\./', Configure::version())) {
            $logPath = LOGS . 'test_debug_' . date('Ymd') . '.log';
        } else {
            // CakePHP 2.1.x
            $logPath = LOGS . 'test_rotate_log_type_' . date('Ymd') . '.log';
        }
        $this->assertTrue(file_exists($logPath));
        $log = file_get_contents($logPath);
        $this->assertTrue(strpos($log, $hash) > 0);
        @unlink($logPath);
    }
}