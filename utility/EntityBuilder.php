<?php

class EntityBuilder extends EntityCrawler {

  private $stats;
  private $dataDriver;
  private $queue = array();
  private $verbose;

  private function getQDP() {
    return Project::GetQDP();
  }

  private function output($message) {
    if (!$this->verbose)
      return;

    print_r($message);
  }

  protected function handleEntity($sourceEntry, $entityName) {

    $entityClassName = $entityName;
    $qdp = $this->getQDP();
    $er = new EntityReflection("$entityClassName", $this->dataDriver);
    list($structure, $indices, $fullTexts) = $er->getStructure();

    if ($structure) {

      if (count($argv)>1 && $entityClassName != $argv[1]) {
        $this->output("Note: Argument list mismatches: $entityClassName\n");
        return;
      }

      $entityModelClassName = "{$entityClassName}Model";

      $rf = new ReflectionClass($entityModelClassName);
      if ($rf->isAbstract()) {
        $this->output("$entityModelClassName is abstract. Skipping.\n");
        return;
      }

      $model = $entityModelClassName::getInstance();
      $entityClassName = strtolower($entityClassName);
      $this->queue[] =
          array($entityClassName, $structure, $indices, $fullTexts);

    } else {
      $this->output("Note: Structure not available for '$entityName'\n");
      $errors = $er->getErrors();
      if (count($errors) > 0) {
        $this->output(implode("\n", $errors)  . "\n");
      }
      $res = "Error";
    }

    $this->stats[$entityClassName] = array($res, $indices);
  }

  public static function BuildEntity($entityClassName,
                                     $dataDriver = null,
                                     $fileSystem = null,
                                     $verbose = true,
                                     $backup = true) {
    if ($fileSystem === null)
      $fileSystem = new FileSystem();

    $rf = new ReflectionClass($entityClassName);
    $filename = $rf->getFilename();
    $dirname = $fileSystem->dirname($filename);

    $builder = new EntityBuilder($fileSystem);
    $builder->build($dirname, $dataDriver, $verbose, $backup);
  }

  public function build($sourceEntry, $dataDriver = null, $verbose = true,
      $backup = true) {

    flush();
    ob_flush();
    ob_end_flush();

    $this->verbose = $verbose;

    $this->resolveProject($sourceEntry);

    if ($dataDriver === null)
      $dataDriver = new MySQLDataDriver();

    $this->dataDriver = $dataDriver;
    $this->traverse($sourceEntry);
    $qdp = $this->getQDP();
    $qdp->execute("SET FOREIGN_KEY_CHECKS=0;");
    $tables = $qdp->getTables();
    $backup_exists = array();

    if (count($this->queue) == 0) {
      $this->output(
          "Note: entity traversal queue is empty for '$sourceEntry'.\n");
    }

    foreach ($this->queue as $descriptor) {
      list($entityClassName, $structure, $indices, $fullTexts) = $descriptor;

      if (count($indices) > 0 && count($fullTexts) > 0) {
        $this->output("Error: Table `$entityClassName`"
            . " cannot have indices and fulltext.\n");

        return;
      }

      $engine = "MyISAM";
      $structure = array_merge($structure, $indices);

      if (in_array($entityClassName, $tables)) {
        if ($backup) {
          $backup_exists[$entityClassName] = true;
          $qdp->execute("create table `backup_{$entityClassName}`
              like `{$entityClassName}`");
          $qdp->execute("insert into `backup_{$entityClassName}`
              (select * from `{$entityClassName}`);");
          $aff = $qdp->getAffectedRowCount();
          $qdp->drop($entityClassName);
          $this->output("Backed up rows for $entityClassName: " . $aff . "\n");
        }
      } else {
        $this->output("Table $entityClassName does not yet exist\n");
      }


      $query = $qdp->prepareTableQuery($entityClassName,
                                       $structure,
                                       $fullTexts,
                                       $engine);
      $qdp->prepareTable($entityClassName,
                         $structure,
                         $fullTexts,
                         $engine);
      $this->output("Created table $entityClassName\n");

      if ($err = $qdp->getError()) {
        $this->output($err."\n");
        $this->output($query."\n");
        preg_match('/\(errno: ([0-9]*)\)/', $err, $matches);
        $docs = glob(dirname(__FILE__) . "/docs/errno.{$matches[1]}.txt");

        if (count($docs) > 0) {
          $error_doc = reset($docs);
          $this->output(file_get_contents( $error_doc ));
        } else {
          $this->output("Unknown issue. Recheck entity structure.");
        }

        // TODO: remove or rename backup table?
        return;
      }
    }

    if ($backup) {
      foreach ($this->queue as $descriptor) {
        list($entityClassName, $structure, $indices) = $descriptor;

        if (!$backup_exists[$entityClassName])
          continue;

        $oldFields = $qdp->getFields("backup_{$entityClassName}");
        $oldFields = "`".implode("`, `", $oldFields)."`";

        $qdp->execute("
            insert into `{$entityClassName}` ({$oldFields})
            (select {$oldFields} from `backup_{$entityClassName}`) ;
        ");
        $aff = $qdp->getAffectedRowCount();
        $this->output(
            "Restored rows for new structure of $entityClassName: $aff\n");

        if ($err = $qdp->getError()) {
          $this->output($err."\n");
          $this->output(substr($qdp->last_query, 400)." ...\n");
        } else {
          $qdp->drop("backup_{$entityClassName}");
        }

        $this->output("\n");
      }
    }

    $qdp->execute("SET FOREIGN_KEY_CHECKS=1;");
  }

}

