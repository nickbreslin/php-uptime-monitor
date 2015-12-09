<?php

if (PHP_SAPI !== 'cli') {
	die('Must be from command line');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', realpath(dirname(__FILE__)).'/');


$host = isset($argv[1]) ? $argv[1] : false;
$port = isset($argv[2]) ? $argv[2] : 80;

if($host === false) {
	die('No host');
}


try {
	check_status($host, $port);
} catch (ErrorException $e) {
    handle_failure($host.$port);
}


function check_status($host, $port = 80)
{
	$waitTimeoutInSeconds = 1; 

	$fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds);

    if ($fp)
    {
    	handle_success($host.$port);
    	fclose($fp);
	}	
	else
	{
	   handle_failure($host.$port);
	}
}







function send_email($subject, $message = "")
{
	return mail ( "nickbreslin@gmail.com" , $subject, $message);
}


function handle_failure($file)
{
	if(lock_exists($file))
	{
		// do nothing
	}
	else
	{
		if(create_lock($file))
		{
			send_email("Downtime Detected for $file");
		
		}
		else
		{
			// could not create lock file
		}
	}
}

function handle_success($file)
{
	if(lock_exists($file))
	{
		if(remove_lock($file))
		{
			send_email("Uptime Detected for $file");
		
		} else {
			// could not remove lock
		}
	}
}

function remove_lock($file)
{
	return unlink($file.".lock");
}

function create_lock($file)
{
	return fopen($file.".lock", "w");
}

function lock_exists($file)
{
	return file_exists(ROOT_PATH.$file.".lock");
}

