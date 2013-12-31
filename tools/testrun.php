#!/usr/local/bin/php
<?
include_once( dirname(__FILE__) . '/../base/Base.php' );
@include_once( dirname(__FILE__) . '/../testproject/project.php' );



$parameters = array(
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

if (isset( $options[ 'coverage' ]  ) || isset( $options[ 'c' ]  ) )
{
	echo "Coverage mode\r\n";
	$coverageMode = true;	
	
} 
else 
{
	$coverageMode = false;
}

array_shift( $argv );


Console::Disable();

TestUnitModule::run( $argv );


if ( $coverageMode ) {

	// print_r( get_included_files() );
}

?>