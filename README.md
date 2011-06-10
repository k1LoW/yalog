# Yalog: Yet Another Logger for CakePHP #

## RotateFileLog ##

### Usage ###

First, put `yalog' directory on app/plugins in your CakePHP application.

Second, add the following code in bootstrap.php.

    <?php
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

First, put `yalog' directory on app/plugins in your CakePHP application.

Second, put log4php source directory on app/plugins/yalog/vendors/log4php in your CakePHP application.

Third, add the following code in bootstrap.php.

    <?php
        CakeLog::config('Log4php',
                    array(
                          'engine' => 'Yalog.Log4php'
                          ));

### Configure ###

Modify following,

- app/plugins/yalog/libs/log/log4php.properties
- Log4php::write() in app/plugins/yalog/libs/log/log4php.php 
        
## Adjust level of log output ##

### Usage ###

add the following code of the level that you want to output the log in bootstrap.php.
        
    <?php
        CakeLog::config('Yalog..OutputLevel', LOG_WARNING);