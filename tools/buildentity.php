#!/usr/bin/env php
<?

include_once(dirname(__FILE__) . "/../base/Base.php");

flush();
ob_flush();
ob_end_flush();

$fs = new FileSystem();
$eb = new EntityBuilder($fs);
$eb->resolveProject($fs->getcwd());

if (in_array("-h", $argv)) {
  echo "Creates a database table for an Entity.\n";
  echo "Usage:\n\t" . basename(__FILE__) . " [entity_dir]\n\n";
  exit(0);
}

if (isset($argv[1]))
  $dir = $fs->realpath(strtolower(trim($argv[1])));
else
  $dir = $fs->getcwd();

$dataDriver = new MySQLDataDriver();

// TODO: return exit status on success/failure. See utility/EntityBuilder.php.
$eb->build($dir, $dataDriver);

?>