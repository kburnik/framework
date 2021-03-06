<?php


class FileStorage extends Storage {
  protected $filename;

  function __construct($filename) {
    $this->filename = $filename;

    // create file if not existing
    if (!file_exists($filename)) {
      $this->store();
    }
    parent::__construct();
  }

  function getData() {
    return $this->data;
  }

  function load() {
    if (file_exists($this->filename)) {
      $contents = file_get_contents($this->filename);
      $contents = substr($contents,10);
      $contents = substr($contents,0,strlen($contents)-3);
      eval('$a = ' . $contents);
      $this->setData( $a );
    } else {
      throw new Exception('Non existing storage file ' . $this->filename);
    }
    $this->onLoad($this->getData());
  }

  function store() {
    // allow storing empty data if file not existing!
    if (file_exists($this->filename) && !$this->hasDataChanged()) return;

    $data = $this->getData();

    $output = var_export($data,true);
    if (!file_put_contents($this->filename,'<?php return '.$output.'; ?>',LOCK_EX)) {
      throw new Exception('Cannot write storage to file!');
    };

     $this->onStore($data);
  }
}

