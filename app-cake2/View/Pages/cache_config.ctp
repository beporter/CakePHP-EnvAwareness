<h2>Dump of all Cache Configs</h2>

<?php
// Make sure debug is on so output is actually printed.
if (!Configure::read('debug')) {
	Configure::write('debug', 1);
}

// Loop over all defined Caches and dump the config for each.
foreach (Cache::configured() as $c) {
	echo '<h3>' . h($c) . '</h3>';
	debug(Cache::config($c));
}
