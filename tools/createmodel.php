#!/usr/bin/env php
<?php

include_once( dirname(__FILE__) . "/../base/Base.php");

$fs = new FileSystem();
$mc = new EntityModelCreator( $fs );
array_shift( $argv );

foreach ( $argv as $entityName )
  $mc->createModel( $entityName );

