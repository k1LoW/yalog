# Yalog: Yet Another Logger for CakePHP #

h2. RotateFileLog

h3. Usage

First, put `yalog' directory on app/plugins in your CakePHP application.
Second, add the following code in bootstrap.php.

<pre>
<?php
    CakeLog::config('otherFile',
                array(
                      'engine' => 'Yalog.RotateFileLog'
                      ));
</pre>

h3. Configure

h4. Rotate

<pre>
<?php
    Configure::write('Yalog.RotateFileLog.weekly', true);
    Configure::write('Yalog.RotateFileLog.rotate', 4);
</pre>

