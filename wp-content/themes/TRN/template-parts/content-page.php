<?php
/**
 * The template used for displaying page content
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen - Modified
 * @since Twenty Sixteen X.0
 */

$template = strtolower(preg_replace("/[^A-Za-z]/", "-", get_the_title()));
WPParseAndGet($template);