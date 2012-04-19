# Yalog: Yet Another Logger for CakePHP2.0RC3 #

## RotateFileLog ##

### Usage ###

First, put `Yalog' directory on app/Plugin in your CakePHP application.

Second, add the following code in bootstrap.php.

    <?php
    
        CakePlugin::load('Yalog');
        // or
        // CakePlugin::loadAll();

        App::uses('CakeLog', 'Log');
        CakeLog::config('RotateFileLog',
                    array(
                          'engine' => 'Yalog.RotateFileLog'
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
        CakeLog::config('Log4php',
                    array(
                          'engine' => 'Yalog.Log4php'
                          ));

### Configure ###

Modify following,

- app/Plugin/Yalog/Lib/Log/Engine/log4php.properties
- Log4php::write() in app/Plugin/Yalog/Lib/Log/Engine/log4php.php 
        
## Adjust level of log output ##

### Usage ###

Add the following code in bootstrap.php.

    <?php
        CakeLog::config('Yalog..OutputLevel', LOG_WARNING);

Set lower level than level that you want to output the log at.
(LOG_ERROR:2 > LOG_WARNING:4 > LOG_NOTICE:5 > LOG_INFO:6 > LOG_DEBUG:7)

In the example, log of "LOG_ERROR", "LOG_WARNING" and the others are output.
        
All output is stopped when it is set to false.
        
    <?php
        CakeLog::config('Yalog..OutputLevel', false);

## License

MIT License
