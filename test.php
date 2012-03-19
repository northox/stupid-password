<?php
// Test Stupid Password
require('StupidPass.class.php');
$list = array(
'football',
'fOOtb4ll',
'pr1nce55',
'b4byg1r1',
'passw0rd',
'P@55W0r6',
'zxcasdqwe',
'zxc45dqw3',
'aPf1#@_GHe'
);

$sp = new StupidPass();
foreach ($list as $pass) {
  $m = ($sp->validate($pass) == false) ? "FAIL: " : "PASS: ";
  print("$m $pass\n");
  #$err = $sp->get_errors();
  #print_r($err);
}
?>
