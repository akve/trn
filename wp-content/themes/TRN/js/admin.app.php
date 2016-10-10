<?php
/**
 * This is just a compression script that combines all the javascript into a coherent package and serves it accordingly
 */

# include the initiation file
include("../../init.php");
define('APP_PATH', dirname(__FILE__)."/");

# header expiries and compression
header('Content-type: text/javascript');
//header("Expires: Sat, 26 Jul 2097 05:00:00 GMT");
ob_start('compress');

# this is where we loop through all the files
$base = scandir(APP_PATH);
$included = array("controller", "directives", "factory", "module", "filters");
foreach($base as $folder)
{
	if ($folder == "." || $folder == "..") continue;
	# we're only going into the modular folders and making strict modules
	if (is_dir($folder))
	{
		# this is where we make the strict modules
		echo "(function(angular) { 'use strict'; ";
		foreach($included as $file)
		{
			$full = APP_PATH."{$folder}/".strtolower($folder).".{$file}.js";
			if (file_exists($full))
				echo file_get_contents($full);
		}
		echo "})(window.angular);";
	}
}

echo "(function(angular) { 'use strict'; ";
# now let''s include the main controller for this app
echo file_get_contents(APP_PATH."admin.controller.js");
# now that we have all the modules, let's load the main app file
echo file_get_contents(APP_PATH."admin.js");
echo "})(window.angular);";

ob_end_flush();
