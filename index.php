<?php
/******
 Generate a ics file from a CSV of the GGAFL Schedule
 Cooper Lees <me@cooperlees.com>
*******/

$EVENT_COUNT = 0;
$EVENTS = array();

$PRODID = '//cooperlees/ggaflcal//NONSGML v1.0//EN';
$TIMEZONE = 'America/Los_Angeles';

date_default_timezone_set($TIMEZONE);

function _dateToCal($timestamp) {
	return date('Ymd\THis', strtotime($timestamp));
}

function _escapeString($string) {
	return preg_replace('/([\,;])/','\\\$1', ($string) ? $string : '');
}

function generate_events()
{
	global $EVENTS, $TIMEZONE;
	foreach ($EVENTS as $k => $v) {
		$aEvent = "BEGIN:VEVENT\r\n".
		"DTSTART;TZID=$TIMEZONE:"._dateToCal($k)."\r\n".
		"DTEND;TZID=$TIMEZONE:"._dateToCal($v['end'])."\r\n".
		"SUMMARY:".$v['title']."\r\n".
#		"LOCATION:".$v['loc']."\r\n".
		"DESCRIPTION:"._escapeString($v['desc'])."\r\n".
		"ORGANIZER:MAILTO:ggafl@googlegroups.com"."\r\n".
#		"X-ALT-DESC;FMTTYPE=text/html:".$this->_escapeString($this->html)."\n".
		"URL:http://www.ggafl.com/schedule/\r\n".
		"UID:ggafl-".uniqid()."\r\n".
		"SEQUENCE:0\r\n".
		"DTSTAMP:"._dateToCal(time())."\r\n".
		"END:VEVENT\r\n";
		echo($aEvent);
	}
}

function generateICS() {
	global $PRODID, $TIMEZONE;
	header('Content-type: text/calendar; charset=utf-8');
	echo("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-$PRODID\r\nMETHOD:REQUEST\r\n");
	echo("X-PUBLISHED-TTL:PT07D\r\nX-WR-CALNAME:GGAFL Events\r\n");
	echo("X-WR-TIMEZONE:$TIMEZONE\r\n");
	echo("X-ORIGINAL-URL:http://cooperlees.com/ggaflcal\r\nX-WR-CALDESC:http://www.ggafl.com/\r\n");
	generate_events();
	echo("END:VCALENDAR\r\n");
}

function loadCSV() {
	global $EVENT_COUNT, $EVENTS;
	$cf = fopen("ggafl-events-2015.csv", "r");
	while (!feof($cf)) {
		$l = fgetcsv($cf);
		if($EVENT_COUNT == 0) { 
			$EVENT_COUNT++; 
			continue;
		}
		$EVENTS[$l[0]] = array(
			'end' => $l[1],
			'title' => $l[2],
			'desc' => $l[3],
		);
		$EVENT_COUNT++;
	}
	fclose($cf);
}

// Load the CSV File into a array
loadCSV();

// Output ICS
generateICS();
?>
