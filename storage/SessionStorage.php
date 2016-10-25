<?php


class SessionStorage extends Storage {

  private $identifier;

  function __construct($identifier = 'default') {
    session_start();
    $this->identifier = $identifier;
    parent::__construct();
  }

  function load() {
    $this->setData( $_SESSION["SessionStorage-{$this->identifier}"] );
  }

  function store() {
    $_SESSION["SessionStorage-{$this->identifier}"] = $this->getData();
  }

  /// static implementation

  private static $instances = array();

  // get a singleton instance of a session storage defined by identifier
  public static function getInstance( $identifier = 'default' ) {
    if (!isset( self::$instances[ $identifier ] )) {
      self::$instances[ $identifier ] = new SessionStorage( $identifier );
    }
    return self::$instances[ $identifier ];
  }
}

