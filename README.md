# Yalog: Yet Another Logger for CakePHP2.x #

[![Build Status](https://secure.travis-ci.org/k1LoW/yalog.png?branch=2.0)](http://travis-ci.org/k1LoW/yalog)

## RotateFileLog ##

### Usage ###

First, put `Yalog' directory on app/Plugin in your CakePHP application.

Second, add the following code in bootstrap.php.

    <?php
    
        CakePlugin::load('Yalog');
        // or
        // CakePlugin::loadAll();

        App::uses('CakeLog', 'Log');
        CakeLog::config('debug', array(
            'engine' => 'Yalog.RotateFileLog',
            'types' => array('notice', 'info', 'debug'),
            'file' => 'debug',
        ));
        CakeLog::config('error', array(
            'engine' => 'Yalog.RotateFileLog',
            'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
            'file' => 'error',
        ));


### Configure ###

#### Rotate ####

    <?php
        Configure::write('Yalog.RotateFileLog.weekly', true);
        Configure::write('Yalog.RotateFileLog.rotate', 4);

## Log4php (Sample) ##

### Usage ###

First, put `Yalog' directory on app/Plugin in your CakePHP application.

Second, put log4php source directory on app/Plugin/Yalog/Vendor/log4php in your CakePHP application.

        http://logging.apache.org/log4php/download.html
        
Third, add the following code in bootstrap.php.

    <?php
        CakeLog::config('debug', array(
            'engine' => 'Yalog.Log4php',
            'types' => array('notice', 'info', 'debug'),
            'file' => 'debug',
        ));
        CakeLog::config('error', array(
            'engine' => 'Yalog.Log4php',
            'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
            'file' => 'error',
        ));

### Configure ###

Modify following,

- app/Plugin/Yalog/Lib/Log/Engine/log4php.properties
- Log4php::write() in app/Plugin/Yalog/Lib/Log/Engine/log4php.php 

## License

MIT License
