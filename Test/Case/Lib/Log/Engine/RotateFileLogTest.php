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
            $prefix = 'test_debug_';
        } else {
            // CakePHP 2.1.x
            $prefix = 'test_rotate_log_type_';
        }
        $logPath = LOGS . $prefix . date('Ymd') . '.log';
        $this->assertTrue(file_exists($logPath));
        $log = file_get_contents($logPath);
        $this->assertTrue(strpos($log, $hash) > 0);
        @unlink($logPath);
    }

    /**
     * testRorateRemoveLog
     *
     * jpn: Yalog.RotateFileLog.rotate以上のファイルができた場合、ファイル名のソート順でファイルを削除する
     */
    public function testRorateRemoveLog(){
        CakeLog::config('test_rotate_log', array(
                                                 'engine' => 'Yalog.RotateFileLog',
                                                 'type' => array('test_rotate_log_type'),
                                                 'file' => 'test_debug',
                                                 ));
        Configure::write('Yalog.RotateFileLog.rotate', 5);
        $hash = sha1(time() . 'testRotateFileLog');
        if (preg_match('/^2\.2\./', Configure::version())) {
            $prefix = 'test_debug_';
        } else {
            // CakePHP 2.1.x
            $prefix = 'test_rotate_log_type_';
        }
        for ($i = 1; $i <= 5; $i++) {
            $logPath = LOGS . $prefix . date('Ymd', strtotime('-' . $i . 'day')) . '.log';
            file_put_contents($logPath, $hash);
        }
        $this->assertIdentical(count(glob(LOGS . $prefix . '*.log')), 5);

        CakeLog::write('test_rotate_log_type', $hash);
        $logPath = LOGS . $prefix . date('Ymd') . '.log';
        $this->assertTrue(file_exists($logPath));
        $this->assertIdentical(count(glob(LOGS . $prefix . '*.log')), 5);

        $logPath = LOGS . $prefix . date('Ymd', strtotime('-5day')) . '.log';
        $this->assertFalse(file_exists($logPath));

        foreach (glob(LOGS . $prefix . '*.log') as $log) {
            @unlink($log);
        }
    }
}