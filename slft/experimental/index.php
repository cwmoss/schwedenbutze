<?php

define('SLOWFOOT_START', microtime(true));
define('SLOWFOOT_BASE', dirname(__DIR__));

require SLOWFOOT_BASE . '/vendor/autoload.php';

require SLOWFOOT_BASE . '/vendor/cwmoss/slowfoot/src/_main/webdeploy.php';

/*
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Allow-Origin, x-slft-deploy");

print "Build started";
print "<br />";
print date("Y-m-d H:i:s");
print "<br />";
if($_SERVER['HTTP_X_SLFT_DEPLOY'] == "123456789") {
  echo "Check passed";
} else {
  echo "PoOp";
}
*/
