# Yalog: Yet Another Logger for CakePHP #

## RotateFileLog ##

### Usage ###

First, put `yalog' directory on app/plugins in your CakePHP application.
Second, add the following code in bootstrap.php.

    <?php
        CakeLog::config('otherFile',
                    array(
                          'engine' => 'Yalog.RotateFileLog'
                          ));

### Configure ###

#### Rotate ####

    <?php
        Configure::write('Yalog.RotateFileLog.weekly', true);
        Configure::write('Yalog.RotateFileLog.rotate', 4);

