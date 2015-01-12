#!/usr/bin/env php
<?
include_once( dirname(__FILE__) . '/../base/Base.php' );

$parameters = array('t' => 'testproject',);

$options = getopt(implode('', array_keys($parameters)), $parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' ||
        preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}

while ($key = array_pop($pruneargv))
  unset($argv[$key]);

$argv = array_values( $argv );

if (isset($options['testing']) || isset( $options['t'] )) {
  @include_once( dirname(__FILE__) . '/../.testproject/project.php' );
}

// Filter for tests. TODO: This is hacky. Do argument handling properly.
if ($index = array_search("-f", $argv)) {
  $options['filter'] = $argv[$index + 1];
  unset($argv[$index]);
  unset($argv[$index + 1]);
  $argv = array_values($argv);
}

$summary = false;
if ($index = array_search("-s", $argv)) {
  $summary = true;
  unset($argv[$index]);
  $argv = array_values($argv);
}

array_shift($argv);

Console::Disable();
$res = TestCase::run($argv, $options['filter'], $summary);
exit($res);

?>
