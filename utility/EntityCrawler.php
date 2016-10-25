<?php

abstract class EntityCrawler {

  private $fileSystem;

  protected abstract function handleEntity($sourceEntry, $entityName);

  public function __construct($fileSystem  = null) {
    if ($fileSystem === null)
      $fileSystem = new FileSystem();

    $this->fileSystem = $fileSystem;
  }

  public function resolveProject($sourceEntry = null) {
    if ($sourceEntry === null)
      $sourceEntry = $this->fileSystem->getcwd();

    $sourceEntry = realpath($sourceEntry);

    // include the project
    $dir = str_replace('\\', '/', $sourceEntry);
    $parts = explode("/", $dir);

    while (!empty($parts)) {
      array_pop($parts);
      $dir = implode("/", $parts);
      $project_file = "$dir/project.php";

      if (file_exists($project_file)) {
        include_once($project_file);

        break;
      }
    }
  }

  protected function traverse($sourceEntry = null) {

    if ($sourceEntry === null)
      $sourceEntry = $this->fileSystem->getcwd();

    $fs = $this->fileSystem;

    if (!$fs->is_dir($sourceEntry)) {
      if (!preg_match('/(.*)Model\.php$/', $sourceEntry))
        return;

      $entityFile = str_replace("Model.php", ".php", $sourceEntry);
      $entityName = trim(str_replace('.php', '', basename($entityFile)));

      if (!is_subclass_of($entityName, 'Entity')) {
        echo "Note: $entityName is not subclass of Entity or not loaded.\n";
        return;
      }

      $this->handleEntity($entityFile, $entityName);

      return;
    }

    if (!file_exists("$sourceEntry/.include"))
      return;

    $files = $fs->glob("$sourceEntry/*");

    foreach ($files as $nextSourceEntry)
      $this->traverse($nextSourceEntry);
  }

}

