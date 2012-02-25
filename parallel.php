<?php

	$arguments = getopt("d:m:");

	$directory = (isset($arguments['d']) ? $arguments['d'] : "");
	$maxParallel = (isset($arguments['m']) ? (int) $arguments['m'] : 5);;

	$currentDirectory = dirname(__FILE__);

	$results = $files = $runningProcesses = array();


	// grab all the *.php files (phpunit tests) in the directory we specified
	foreach (glob($directory . "*.php") as $filename) {
		$filepath = realpath($currentDirectory) . "/" . $filename;

	    if ($filepath === __FILE__) {
	    	// don't run this script again :)
	    	continue;
	    }

	    $files[] = $filepath;
	}

	if (empty($files)) {
		die("No test files found");
	}

	$i = 0;

	while ($i < sizeof($files)) {
		if ($i >= $maxParallel) {
			foreach ($runningProcesses as $key => $process) {
				$status = proc_get_status($process['handle']);
        		if ($status['running'] !== true) {
        			unset($runningProcesses[$key]);
        			unset($files[$process['nr']]);
        			$files = array_values($files);
        			$i--;

				    // close the process
				    proc_close($process['handle']);
        		}
			}
			continue;
		}

		$descriptorspec = array(
		   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		   2 => array("pipe", "w") // stderr
		);

		$pipes = array();
		$process = proc_open('phpunit ' . $files[$i], $descriptorspec, $pipes, null, null);

		if (is_resource($process)) {
			$runningProcesses[] = array('handle' => $process, 'nr' => $i);

			$results[$files[$i]] = stream_get_contents($pipes[1]);

		    // It is important that you close any pipes before calling
		    // proc_close in order to avoid a deadlock
		    fclose($pipes[1]);
		}

		$i++;

		if (empty($files)) {
			break;
		}
	}

	// tests ended - display results
	foreach ($results as $testFileName => $result) {
		echo "*** Results for [" . $testFileName . "]: \n" . $result . "\n\n";
	}