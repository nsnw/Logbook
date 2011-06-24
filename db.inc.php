<?php

// core/db.inc.php
//
// Functions for connecting to a DB. Uses ADODb.

// include ADODb functions
require_once('/usr/share/php/adodb/adodb.inc.php');
require_once('config.inc.php');

// define DB variables

// function to set up DB connections
function db_create()
{
	// inherit DB vars

	global $DB_TYPE;
	global $DB_HOST;
	global $DB_USER;
	global $DB_PASS;
	global $DB_NAME;

	// create ADODb connection and connect
	$conn = &ADONewConnection($DB_TYPE);
	$conn->PConnect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

	// if successful, return the connection link, otherwise return an error
	if($conn)
	{
		return $conn;
	}
	else
	{
		return "DBCONNFAILED";
	}
}

?>
