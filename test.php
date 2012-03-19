<?php
// Test Stupid Password
require('StupidPass.class.php');
$list = array(
'football',
'fOOtb4ll',
'pr1nce55',
'b4byg1r1',
'passw0rd',
'P@ssw0rd',
'zxcasdqwe',
'zxc45dqw3'
);

$sp = new StupidPass();
foreach ($list as $pass) {
  $m = ($sp->validate($pass) == false) ? "FAIL: " : "PASS: ";
  print("$m $pass\n");
}
?>
