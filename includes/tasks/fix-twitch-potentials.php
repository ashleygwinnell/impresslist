<?php

set_time_limit(0);

//
// Refresh Youtuber subs/views count.php
//
$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");


print_r(twitch_getUsers("44019518"));
echo "<br/><br/>";
print_r(twitch_getUsersFromLogin("forcehabit"));

die();

$potentials = $db->query("SELECT * FROM twitchchannel_coverage_potential;");

echo "<b>Count:</b> " . count($potentials) . "<br/>";

for($i = 0; $i < count($potentials); $i++) {
	$p = $potentials[$i];

	$matches = $db->query("SELECT * FROM twitchchannel_coverage WHERE url = '" . $p['url'] . "' AND game = '" . $p['game'] . "' LIMIT 1; ");

	echo "- Count matches: " . count($matches) . "<br/>";
	if (count($matches) == 1) {
		$match = $matches[0];
		if (!$p['coverage'] || !$p['removed']) {
			$db->query("UPDATE twitchchannel_coverage_potential SET coverage = '" . $match['id'] . "', removed = '1' WHERE id = '" . $p['id'] . "' LIMIT 1;");
		}
		echo "    - Matched: " . $p['title'] . ": " . $p['url'] ."<br/>";
	}
	else {
		echo "    - Not matched: " . $p['title'] . ": " . $p['url'] ."<br/>";
	}
}

die();
?>
