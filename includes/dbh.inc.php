<?php

$servername = "localhost";
$dBUsername = "root";
$dBPassword = "";
$dBName = "fabonline";

$reportingServername = "localhost";
$reportingDBUsername = "root";
$reportingDBPassword = "";
$reportingDBName = "fabonline";

$dBPassword = apache_getenv('DB_PW');

$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

function GetDBConnection()
{
	global $servername, $dBUsername, $dBPassword, $dBName;
	return mysqli_connect($servername, $dBUsername, $dBPassword, $dBName);
}

function GetReportingDBConnection()
{
	global $reportingServername, $reportingDBUsername, $reportingDBPassword, $reportingDBName;
	return mysqli_connect($reportingServername, $reportingDBUsername, $reportingDBPassword, $reportingDBName);
}
