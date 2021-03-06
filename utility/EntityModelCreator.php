<?php

class EntityModelCreator {

  private $fileSystem;

  public function __construct($fileSystem = null) {
    if ($fileSystem === null)
      $fileSystem = new FileSystem();

    $this->fileSystem = $fileSystem;
  }

  private function glob_files($dir) {
    $fs = $this->fileSystem;

    return array_diff(
        array_merge($fs->glob("$dir/*"),  $fs->glob("$dir/.*")),
        array("$dir/.", "$dir/.."));
  }

  private function copy_recursively($sourceDir,
                                    $destinationDir,
                                    $replacements) {

    $fs = $this->fileSystem;

    if (!$fs->file_exists($destinationDir))
      $fs->mkdir($destinationDir);

    $files = $this->glob_files($sourceDir);

    foreach ($files as $sourceEntry) {

      $sourceEntryBasename = substr($sourceEntry, strlen($sourceDir)+1);

      if ($fs->is_dir($sourceEntry)) {
        $dir = $destinationDir . '/' . $sourceEntryBasename;
        $this->copy_recursively($sourceEntry, $dir, $replacements);
      } else {
        $file = $destinationDir . '/' . $sourceEntryBasename;

        $destinationFile = strtr($file, $replacements);

        if (file_exists($destinationFile))
          continue;

        $fs->copy($sourceEntry, $destinationFile);

        $replacedContent =
            strtr($fs->file_get_contents($destinationFile), $replacements);

        $fs->file_put_contents($destinationFile, $replacedContent);
      }
    }

  }

  public function createModel($entityName, $destinationDir = null,
      $templateDir = null, $replacements = null) {

    if ($destinationDir === null)
      $destinationDir = $this->fileSystem->getcwd();

    if ($templateDir === null)
      $templateDir = dirname(__FILE__)."/templates/EntityModel";

    if ($replacements === null)
      $replacements = array("[entityName]" => ucfirst($entityName));

    $modelDir = $destinationDir."/".strtolower($entityName);
    $this->copy_recursively($templateDir, $modelDir, $replacements);
  }

}

