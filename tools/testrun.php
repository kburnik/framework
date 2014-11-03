#!/usr/bin/env php
<?
include_once( dirname(__FILE__) . '/../base/Base.php' );


$parameters = array(
  't' => 'testproject',
  'c' => 'coverage',
  /*
  'r:' => 'required:',
  'o::' => 'optional::',
  */
);

$options = getopt(implode('', array_keys($parameters)), $parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while ($key = array_pop($pruneargv)) unset($argv[$key]);
$argv = array_values( $argv );

if (isset($options['testing']) || isset( $options['t'] ) )
{
  @include_once( dirname(__FILE__) . '/../.testproject/project.php' );
}

if (isset( $options[ 'coverage' ]  ) || isset( $options[ 'c' ]  ) )
{
  echo "Coverage mode\r\n";
  $coverageMode = true;

}
else
{
  $coverageMode = false;
}

// Filter for tests. TODO: This is hacky. Do argument handling properly.
if ($index = array_search("-f", $argv)) {
  $options['filter'] = $argv[$index + 1];
  unset($argv[$index]);
  unset($argv[$index + 1]);
  $argv = array_values($argv);
}

array_shift( $argv );

Console::Disable();

TestUnitModule::run( $argv , $options['filter']);

$testFiles = array( __FILE__  ,  realpath( dirname(__FILE__). '/../utility/auxiliary.php' ) );

foreach ( $argv as $file )
{
  $testFiles[] = realpath( $file );
}

$sourceCodeFiles =  array_diff( get_included_files() , $testFiles );

if ( $coverageMode )
{

  foreach ( $sourceCodeFiles as $file )
    TestCoverage::addCoverageCallsToFile( $file );


  echo "Coverage has been added; rerun the tests to get coverage report\n";
  die();
}

foreach ( $sourceCodeFiles as $file )
    TestCoverage::removeCoverageCallsFromFile( $file );

TestCoverage::ShowResults();

?>
