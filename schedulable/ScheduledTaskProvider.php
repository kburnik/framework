<?php

class ScheduledTaskProvider implements IScheduledTaskProvider {

  private $fileSystem;
  private $directory;

  public function __construct($directory = null,
                              IFileSystem $fileSystem = null) {
    if ($directory === null)
      $directory = Project::GetProjectDir('/gen/tasks');

    if ($fileSystem === null)
      $fileSystem = new FileSystem();

    $this->fileSystem = $fileSystem;
    $this->directory = $directory;

    if (!$this->fileSystem->file_exists($this->directory)) {
      $created = $this->fileSystem->mkdir($this->directory, 0755, true);
      assert($created);
    }

  }

  public function addTask(ITask $task, $arguments, $executeAfter = null) {
    $targetSubdir = date("Y/m/d", strtotime($executeAfter));
    $timestamp = date("His", strtotime($executeAfter));
    $targetDirectory = $this->directory . "/" . $targetSubdir;

    if (!$this->fileSystem->file_exists($targetDirectory)) {
      $created = $this->fileSystem->mkdir($targetDirectory, 0755, true);
      assert($created);
    }

    $index = count($this->fileSystem->glob($targetDirectory . "/*"));
    $zeroPrefixedIndex = sprintf('%07d', $index);
    $targetBasename = $timestamp . "-" . $zeroPrefixedIndex;
    $taskKey = $targetSubdir . "/" . $targetBasename;
    $taskClassName = get_class($task);
    $taskDefinition =
        array($taskKey, $taskClassName, $arguments, $executeAfter);
    $taskFileContents = "<?php return " .
        var_export($taskDefinition, true) . ";\n" ;
    $targetPath = $targetDirectory . "/" . $targetBasename;
    $writtenBytes =
        $this->fileSystem->file_put_contents($targetPath, $taskFileContents);

    return $writtenBytes == strlen($taskFileContents);
  }

  private static function GetLockedName($taskKey) {
    self::CheckTaskKey($taskKey);
    $lockedName =
        preg_replace("/^(.*?)([0-9]{6}-[0-9]{7})$/", '$1.$2', $taskKey);
    return $lockedName;
  }

  public function lockTaskAt($taskKey) {
    $lockedName = self::GetLockedName($taskKey);
    assert($this->fileSystem->file_exists($this->directory . "/" . $taskKey));
    $renamed = $this->fileSystem->rename($this->directory . "/" . $taskKey,
                                         $this->directory . "/" . $lockedName);
    return $renamed;
  }

  public function unlockTaskAt($taskKey) {
    $lockedName = self::GetLockedName($taskKey);
    assert($this->fileSystem->file_exists($this->directory . "/" .
                                          $lockedName));
    $renamed = $this->fileSystem->rename($this->directory . "/" . $lockedName,
                                         $this->directory . "/" . $taskKey);
    return $renamed;
  }

  public function deleteTaskAt($taskKey) {
    self::CheckTaskKey($taskKey);

    $taskPath =
        $this->fileSystem->realpath($this->directory . "/" . $taskKey);

    if (!$this->fileSystem->file_exists($taskPath))
      return false;

    $deleted = $this->fileSystem->unlink($taskPath);

    $path = str_replace("\\", "/", $taskKey);

    $parts = explode("/", $path);
    assert(count($parts) == 4);

    // Clean up directories.
    while (true) {
      array_pop($parts); // First iteration removes basename.

      if (count($parts) == 0)
        break;

      $dirPath = $this->directory . "/" . implode("/", $parts);

      // TODO: faster check if directory is empty.
      if (count($this->fileSystem->glob($dirPath . "/*")) > 0)
        break;

      if (count($this->fileSystem->glob($dirPath . "/.*")) > 0)
        break;

      $removedDir = $this->fileSystem->rmdir($dirPath);
      assert($removedDir);
    }

    return $deleted;
  }

  private function findTasks() {
    return $this->fileSystem->glob($this->directory . "/*/*/*/*");
  }

  // yield $time => array($index, $task, $arguments)
  public function enumerate() {
    foreach ($this->findTasks() as $taskPath) {
      $taskDefinition = include($taskPath);
      $taskPath = str_replace("\\", "/", $taskPath);
      $rootDir = str_replace("\\", "/", $this->directory);

      list($taskKey, $taskClassName, $arguments, $executeAfter) =
          $taskDefinition;

      $instance = new $taskClassName();
      assert($instance instanceOf ITask);

      yield $executeAfter => array($taskKey, $instance, $arguments);
    }
  }

  public function count() {
    return count($this->findTasks());
  }

  private static function CheckTaskKey($taskKey) {
    if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2}\/[0-9]{6}\-[0-9]{7}$/',
        $taskKey)) {
      throw new Exception("Wrong task key format: $taskKey");
    }
  }

}
