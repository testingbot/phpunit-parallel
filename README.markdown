PHPUnit Parallel Testing
====================
This repository contains an attempt to do parallel testing with PHPUnit.
The PHP file will loop over the PHPUnit files in the directory you specify and run PHPUnit in separate processes.
You can specify how many tests you want to run at the same time. 

When all the tests have run, the php file will echo the output of the tests.

Arguments
---------------------
Run with php parallel.php
Options are:
-d directory/
-m maxParallelTests
