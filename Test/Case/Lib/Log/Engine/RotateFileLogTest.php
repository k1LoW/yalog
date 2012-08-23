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
        CakeLog::write('test_log_type', 'testFileLog');
        $logPath = LOGS . 'test_debug.log';
        $this->assertTrue(file_exists($logPath));
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
        CakeLog::write('test_rotate_log_type', 'testRotateFileLog');
        $logPath = LOGS . 'test_debug_' . date('Ymd') . '.log';
        $this->assertTrue(file_exists($logPath));
        @unlink($logPath);
    }
}