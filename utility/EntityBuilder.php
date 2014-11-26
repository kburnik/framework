<?

class EntityBuilder extends EntityCrawler {

  private $stats;
  private $dataDriver;
  private $queue = array();

  private function getQDP() {
    return Project::GetQDP();
  }

  protected function handleEntity($sourceEntry, $entityName) {

    $entityClassName = $entityName;
    $qdp = $this->getQDP();
    $dd = $this->dataDriver;
    $er = new EntityReflection("$entityClassName", $dd);
    list($structure, $indices, $fullTexts) = $er->getStructure();

    if ($structure) {

      if (count($argv)>1 && $entityClassName != $argv[1]) {
        echo "Note: Argument list mismatches: $entityClassName\n";
        continue;
      }

      $entityModelClassName = "{$entityClassName}Model";
      $model = $entityModelClassName::getInstance();
      $entityClassName = strtolower($entityClassName);
      $this->queue[] =
          array($entityClassName, $structure, $indices, $fullTexts);

    } else {
      echo "Note: Structure not available for '$entityName'\n";
      $errors = $er->getErrors();
      if (count($errors) > 0) {
        echo implode("\n", $errors)  . "\n";
      }
      $res = "Error";
    }

    $this->stats[$entityClassName] = array($res, $indices);
  }

  public function build($sourceEntry, $dataDriver = null) {

    flush();
    ob_flush();
    ob_end_flush();

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
      echo "Note: entity traversal queue is empty for '$sourceEntry'.\n";
    }

    foreach ($this->queue as $descriptor) {
      list($entityClassName, $structure, $indices, $fullTexts) = $descriptor;

      if (count($indices) > 0 && count($fullTexts) > 0) {
        echo "Error: Table `$entityClassName`"
            . " cannot have indices and fulltext.\n";

        return;
      }

      $engine = (count($fullTexts) > 0) ? "MyISAM" : "InnoDB";

      $structure = array_merge($structure, $indices);

      if (in_array($entityClassName, $tables)) {
        $backup_exists[$entityClassName] = true;
        $qdp->execute("create table `backup_{$entityClassName}`
            like `{$entityClassName}`");
        $qdp->execute("insert into `backup_{$entityClassName}`
            (select * from `{$entityClassName}`);");
        $aff = $qdp->getAffectedRowCount();
        $qdp->drop($entityClassName);
        print_r("Backed up rows for $entityClassName: " . $aff . "\n");
      } else {
        echo "Table $entityClassName does not yet exist\n";
      }


      $query = $qdp->prepareTableQuery($entityClassName,
                                       $structure,
                                       $fullTexts,
                                       $engine);
      $qdp->prepareTable($entityClassName,
                         $structure,
                         $fullTexts,
                         $engine);
      echo "Created table $entityClassName\n";

      if ($err = $qdp->getError()) {
        echo $err."\n";
        echo $query."\n";
        preg_match('/\(errno: ([0-9]*)\)/', $err, $matches);
        $docs = glob(dirname(__FILE__) . "/docs/errno.{$matches[1]}.txt");

        if (count($docs) > 0) {
          $error_doc = reset($docs);
          echo file_get_contents( $error_doc );
        } else {
          echo "Unknown issue. Recheck entity structure.";
        }

        // TODO: remove or rename backup table?
        return;
      }
    }

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
      echo "Restored rows for new structure of $entityClassName: $aff\n";

      if ($err = $qdp->getError()) {
        echo $err."\n";
        echo substr($qdp->last_query, 400)." ...\n";
      } else {
        $qdp->drop("backup_{$entityClassName}");
      }

      echo "\n";
    }

    $qdp->execute("SET FOREIGN_KEY_CHECKS=1;");
  }

}

?>