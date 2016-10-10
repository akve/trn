<?php
/**
 * Cron that checks for reviews
 */
echo $_SERVER['DOCUMENT_ROOT'];
include('../../../wp-load.php');

$amazon = GetClass('AMAZONCRON');
//$amazon->CheckReviews();
