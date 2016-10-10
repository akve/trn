<?php
# simple remote file
include('../../../wp-load.php');

//include('testinit.php');
$action = $_REQUEST['a'];

# kill it if there's no action
if (!$action || $action == "") exit();

$remote = GetClass('REMOTE');
$remote->$action();