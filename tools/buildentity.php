#!/usr/bin/env php
<?

include_once(dirname(__FILE__) . "/../base/Base.php");

flush();
ob_flush();
ob_end_flush();

$fs = new FileSystem();
$ev = new EntityBuilder($fs);
$ev->resolveProject($fs->getcwd());

if (in_array("-h", $argv)) {
  echo "Creates database tables for an Entity.\n";
  echo "Usage:\n\t" . basename(__FILE__) . " [entity_dir]\n\n";
  exit(0);
}

if (isset($argv[1]))
  $dir = $fs->realpath(strtolower($argv[1]));
else
  $dir = $fs->getcwd();

$dataDriver = new MySQLDataDriver();

// TODO: return exit status on success/failure. See utility/EntityBuilder.php.
$ev->build($dir, $dataDriver);

?>