<?php
App::uses('CakeLog', 'Log');
App::uses('FluentLog', 'Yalog.Log');
class FluentLogTestCase extends CakeTestCase {

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
     * testFluentLog
     *
     */
    public function testFluentLog(){
        CakeLog::config('test_fluent_log', array(
                'engine' => 'Yalog.FluentLog',
                'type' => array('debug.type'),
                'file' => 'test_debug',
            ));
        $hash = sha1(time() . 'testFluentLog');
        $result = CakeLog::write('debug.type', $hash);
        $this->assertTrue($result);
    }

    /**
     * testMultiLog
     *
     */
    public function testMultiLog(){
        CakeLog::config('test_log', array(
                'engine' => 'FileLog',
                'type' => array('debug.type'),
                'file' => 'test_debug',
            ));
        CakeLog::config('test_fluent_log', array(
                'engine' => 'Yalog.FluentLog',
                'type' => array('debug.type'),
            ));
        $hash = sha1(time() . 'testMultiLog');
        $result = CakeLog::write('debug.type', $hash);
        $this->assertTrue($result);

        if (preg_match('/^2\.1\./', Configure::version())) {
            // CakePHP 2.1.x
            $logPath = LOGS . 'debug.type.log';
        } else {
            $logPath = LOGS . 'test_debug.log';
        }
        $this->assertTrue(file_exists($logPath));
        $log = file_get_contents($logPath);
        $this->assertTrue(strpos($log, $hash) > 0);
        @unlink($logPath);
    }
}