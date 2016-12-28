<?php

class XMLFileStorage extends Storage {
  protected $filename;

  function __construct($filename) {
    $this->filename = $filename;
    parent::__construct();
  }

  function load() {
    if (file_exists($this->filename)) {
      $contents = get_once($this->filename);
      $contents = xml_to_array($contents);
      $this->setData($contents);
    } else {
      throw new Exception('Non existing storage file ' . $this->filename);
    }
    $this->onLoad($this->getData());
  }

  function store() {
    if (!$this->hasDataChanged())
      return;

    $data = $this->getData();

    if (!file_put_contents($this->filename, array_to_xml($data))) {
      throw new Exception('Cannot write xml storage to file!');
    };

    $this->onStore($data);
  }
}

