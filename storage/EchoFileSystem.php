<?php

class EchoFileSystem implements IFileSystem {
  protected static function __echo( $shifts = 1 )
  {

    $db = debug_backtrace();

    while ( $shifts-- )
      array_shift( $db );

    $entry = reset( $db );


    $sc = ShellColors::getInstance();


    $function = $entry['class'] . "::" . $entry['function'];
    $function_colored = $sc->getColoredString( $function , "yellow" );

    $args = implode( ', ' , $entry['args'] );
    $args_colored = $sc->getColoredString( $args , "cyan" );

    $line_colored = $function_colored ."( " . $args_colored . " )";

    echo "{$line_colored}\n";

  }


  /// IFileSystem

  public function basename( $path, $suffix = null ) { self::__echo();  }

  public function chdir( $directory ) { self::__echo();  }

  public function chgrp( $filename, $group ) { self::__echo();  }

  public function chown( $filename, $user ) { self::__echo();  }

  public function chmod( $filename, $mode ) { self::__echo();  }

  public function clearstatcache($clear_realpath_cache = false,
                                 $filename = null) {
    self::__echo();
  }

  public function copy( $source, $dest, $context = null ) { self::__echo();  }

  public function delete( $filename, $context = null ) { self::__echo();  }

  public function dirname( $path ) { self::__echo();  }

  public function disk_free_space( $directory ) { self::__echo();  }

  public function disk_total_space( $directory ) { self::__echo();  }

  public function diskfreespace( $directory ) { self::__echo();  }

  public function fclose( $handle ) { self::__echo();  }

  public function feof( $handle ) { self::__echo();  }

  public function fflush( $handle ) { self::__echo();  }

  public function fgetc( $handle ) { self::__echo();  }

  public function fgets( $handle, $length = 0 ) { self::__echo();  }

  public function fgetss( $handle, $length = 0, $allowed_tags = array() ) {
    self::__echo();
  }

  public function file_exists( $filename ) { self::__echo();  }

  public function file_get_contents($filename, $flags=0, $context=null,
      $offset = 0, $maxlen = 10000000 ) {
    self::__echo();
  }

  public function file_put_contents($file, $data, $flags = 0, $context = null) {
    self::__echo();
  }

  public function file($filename,$flags=0,$context = null) { self::__echo();  }

  public function fileatime( $filename ) { self::__echo();  }

  public function filectime( $filename ) { self::__echo();  }

  public function filegroup( $filename ) { self::__echo();  }

  public function fileinode( $filename ) { self::__echo();  }

  public function filemtime( $filename ) { self::__echo();  }

  public function fileowner( $filename ) { self::__echo();  }

  public function fileperms( $filename ) { self::__echo();  }

  public function filesize( $filename ) { self::__echo();  }

  public function filetype( $filename ) { self::__echo();  }

  public function flock( $handle , $operation , & $wouldblock ) {
    self::__echo();
  }

  public function fnmatch( $pattern, $filename, $flags = 0 ) {
    self::__echo();
  }

  public function fopen($filename, $mode, $use_include_path = false,
                        $context = null) {
    self::__echo();
  }

  public function fpassthru( $handle ) { self::__echo();  }

  public function fputcsv($handle, $fileds, $delimiter=",", $enclosure = '"') {
    self::__echo();
  }

  public function fputs( $handle, $string, $length = 0 ) { self::__echo();  }

  public function fread( $handle, $length ) { self::__echo();  }

  public function fscanf( $handle, $format ) { self::__echo();  }

  public function fseek( $handle, $offset, $whence = SEEK_SET ) {
    self::__echo();
  }

  public function fstat( $handle ) { self::__echo();  }

  public function ftell( $handle ) { self::__echo();  }

  public function ftruncate( $handle , $size ) { self::__echo();  }

  public function fwrite( $handle, $string, $length = 0) { self::__echo();  }

  public function getcwd() { self::__echo();  }

  public function glob( $pattern, $flags = 0 ) { self::__echo();  }

  public function is_dir( $filename ) { self::__echo();  }

  public function is_executable( $filename ) { self::__echo();  }

  public function is_file( $filename ) { self::__echo();  }

  public function is_link( $filename ) { self::__echo();  }

  public function is_readable( $filename ) { self::__echo();  }

  public function is_uploaded_file( $path ) { self::__echo();  }

  public function is_writable( $filename ) { self::__echo();  }

  public function is_writeable( $filename ) { self::__echo();  }

  public function lchgrp( $filename, $group ) { self::__echo();  }

  public function lchown( $filename, $owner ) { self::__echo();  }

  public function link( $from_path, $to_path ) { self::__echo();  }

  public function linkinfo( $path ) { self::__echo();  }

  public function lstat( $filename ) { self::__echo();  }

  public function mkdir( $pathname , $mode = 0777 , $recursive = false ,
      $resource = null ) {
    self::__echo();
  }

  public function move_uploaded_file( $filename , $destination ) {
    self::__echo();
  }

  public function parse_ini_file( $filename, $process_sections = false,
      $scanner_mode = INI_SCANNER_NORMAL ) {
    self::__echo();
  }

  public function parse_ini_string( $ini, $process_sections = false,
      $scanner_mode = INI_SCANNER_NORMAL ) {
    self::__echo();
  }

  public function pathinfo( $path, $options ) { self::__echo();  }

  public function pclose( $handle ) { self::__echo();  }

  public function popen( $command , $mode ) { self::__echo();  }

  public function readfile( $filename, $use_include_path = false,
      $context = null ) {
    self::__echo();
  }

  public function readlink( $path ) { self::__echo();  }

  public function realpath_cache_get() { self::__echo();  }

  public function realpath_cache_size() { self::__echo();  }

  public function realpath( $path ) { self::__echo();  }

  public function rename( $old_name, $new_name, $context = null ) {
    self::__echo();
  }

  public function rewind( $handle ) { self::__echo();  }

  public function rmdir( $dirname, $context = null ) { self::__echo();  }

  public function set_file_buffer( $stream , $buffer ) { self::__echo();  }

  public function stream_set_write_buffer( $stream , $buffer ) {
    self::__echo();
  }

  public function stat($filename) { self::__echo();  }

  public function symlink( $target , $link ) { self::__echo();  }

  public function tempnam( $dir , $prefix ) { self::__echo();  }

  public function tmpfile() { self::__echo();  }

  public function touch( $filename, $time, $atime) { self::__echo();  }

  public function umask( $mask ) { self::__echo();  }

  public function unlink($filename, $context = null) { self::__echo();  }

  public function scandir($directory,
                          $sorting_order = SCANDIR_SORT_ASCENDING,
                          $context = null) {
    self::__echo();
  }

}
