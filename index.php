<?
 
 
require_once 'kernel/fusion.php';

// Instantiate the framework
$fusion = new fusion();

$fusion->devmode(); 	// enables php error reporting and stuff
$fusion ->init();		// runs fusion, brings it ready to do something

$fusion ->render();		// renders page with fusion
// and that's it... an entire web framework