<?php
/**
 *  My own functions that i'm using to customize this theme
 */

//define some basics
error_reporting(E_ALL ^ E_NOTICE || E_STRICT);
// error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
date_default_timezone_set('America/New_York');
ini_get('date.timezone');

//lets define some constants
define('BASE_PATH', dirname(__FILE__));
define('CLASS_PATH', dirname(__FILE__)."/includes/");
define('TEMPLATE_PATH',dirname(__FILE__)."/templates/");
define('EMAIL_TEMPLATE_PATH',dirname(__FILE__)."/templates/emails/");
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'perfedk3_trn');

/** MySQL database username */
define('DB_USER', 'perfedk3_trn');

/** MySQL database password */
define('DB_PASSWORD', '%Q8[oT0_g8B!');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

# Database failed in unexpected manner, should
# only be raised on logic errors but may also
# need raising if the DB connection drops, etc
class DatabaseError extends Exception {}
# The integrity of the database is violated
# by something missing or corrupt; should never
# come up, but if it does it should be addressed
class IntegrityError extends Exception {}

//create a database PDO
function GetDB(){
	$dbhost = DB_HOST;
	$user = DB_USER;
	$pass = DB_PASSWORD;
	$dbname = DB_NAME;
	$constr = 'mysql:host=' . $dbhost . ';dbname=' . $dbname;
	try {
		$database = new PDO($constr, $user, $pass);
		$database->beginTransaction();
		return $database;
	} catch (PDOException $e) {
		throw new DatabaseError("Could not connect to database");
	}
}
function BasicQuery($s, $vars = array())
{
	$db = GetDB();
	$q = $db->prepare($s);

	# bind all variables the proper way
	foreach ($vars as $k => &$v)
	{
		$q->bindParam(':'.$k, $v);
	}

	if (!$q->execute())
		return false;

	return $q;
}

function FetchQuery($s, $vars = array())
{
	$q = BasicQuery($s, $vars);
	$r = $q->fetchAll();

	return $r;
}

function FetchOneQuery($s, $vars = array())
{
	$r = FetchQuery($s, $vars);
	$r = CleanPDO($r[0]);

	return $r;
}


function UpdateQuery($s, $vars = array())
{
	$db = GetDB();
	$q = $db->prepare($s);

	# bind all variables the proper way
	foreach ($vars as $k => &$v)
	{
		$q->bindParam(':'.$k, $v);
	}

	if (!$q->execute())
		return false;

	$db->commit();

	return $q;
}

function DBInsert($table, $insert)
{
	$db = GetDB();
	$cols = array();
	$values = array();

	foreach($insert as $k => $v)
	{
		$cols[] = "`$k`";
		$values[] = ":$k";
	}

	$cols = implode(",", $cols);
	$values = implode(",", $values);

	$i = "INSERT INTO {$table} ($cols) VALUES($values)";

	$q = $db->prepare($i);
	foreach ($insert as $k => &$v)
	{
		$q->bindParam(':'.$k, $v);
	}

	if (!$q->execute())
		return false;
	if ($q->rowCount() > 1)
		return false;

	$id = $db->lastInsertId();
	$db->commit();

	return $id;
}

function CleanPDO($r)
{
	foreach($r as $k => $v) {
		if (is_numeric($k)) {
			unset($r[$k]);
		}
	}

	return $r;
}

//Functions that will be used across all classes
function GetClass($class)
{
	if (class_exists($class)) {
		$return = new $class;
		return $return;
	} else {
		//lets try to get the class from the class file
		if (is_readable(CLASS_PATH."class.".strtolower($class).".php")) {
			require_once(CLASS_PATH."class.".strtolower($class).".php");
			$return = new $class;

			return $return;
		}
	}
}

function RD($p) {
	echo "<pre>",print_r($p),"</pre>";
}

function Curl($url, $post=false)
{
	$ch = curl_init();
	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources
	curl_close($ch);
	return $output;
}

function WPParseAndGet($template, $r = false)
{
	if (file_exists(TEMPLATE_PATH."{$template}.html")) {
		$file = file_get_contents(TEMPLATE_PATH."{$template}.html");
		$file = preg_replace("/%%TEMPLATE_Directory%%/", "http://www.trustreviewnetwork.com//wp-content/themes/TRN", $file);

		if ($r) 
			return $file;
		else
			echo $file;
	} else {
		echo "";
	}
}

function JSONOutput($output)
{
	if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
	header('Content-type: application/json');
	header('Content-Encoding: gzip');
	echo json_encode($output);
}

function DEV()
{
	//BAPO - IP
	if ($_SERVER['REMOTE_ADDR'] === "99.250.150.188") { return true; }
	//Robin - IP
	if ($_SERVER['REMOTE_ADDR'] === "99.226.215.58") { return true; }

	return false;
}