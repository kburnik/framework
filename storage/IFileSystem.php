<?

interface IFileSystem {
  public function basename($path, $suffix = null);
  public function chdir($directory);
  public function chgrp($filename, $group);
  public function chown($filename, $user);
  public function chmod($filename, $mode);
  public function clearstatcache($clear_realpath_cache = false,
                                 $filename = null);
  public function copy($source, $dest, $context = null);
  public function delete($filename, $context = null);
  public function dirname($path);
  public function disk_free_space($directory);
  public function disk_total_space($directory);
  public function diskfreespace($directory);
  public function fclose($handle);
  public function feof($handle);
  public function fflush($handle);
  public function fgetc($handle);
  public function fgets($handle, $length = 0);
  public function fgetss($handle, $length = 0, $allowed_tags = array());
  public function file_exists($filename);
  public function file_get_contents($filename,
                                    $flags=0,
                                    $context=null,
                                    $offset = 0,
                                    $maxlen = 10000000);
  public function file_put_contents($file, $data, $flags = 0, $context = null);
  public function file($filename, $flags=0, $context = null);
  public function fileatime($filename);
  public function filectime($filename);
  public function filegroup($filename);
  public function fileinode($filename);
  public function filemtime($filename);
  public function fileowner($filename);
  public function fileperms($filename);
  public function filesize($filename);
  public function filetype($filename);
  public function flock($handle, $operation, &$wouldblock);
  public function fnmatch($pattern, $filename, $flags = 0);
  public function fopen($filename,
                        $mode,
                        $use_include_path = false,
                        $context = null);
  public function fpassthru($handle);
  public function fputcsv($handle, $fileds, $delimiter=", ", $enclosure = '"');
  public function fputs($handle, $string, $length = 0);
  public function fread($handle, $length);
  public function fscanf($handle, $format);
  public function fseek($handle, $offset, $whence = SEEK_SET);
  public function fstat($handle);
  public function ftell($handle);
  public function ftruncate($handle, $size);
  public function fwrite($handle, $string, $length = 0);
  public function getcwd();
  public function glob($pattern, $flags = 0);
  public function is_dir($filename);
  public function is_executable($filename);
  public function is_file($filename);
  public function is_link($filename);
  public function is_readable($filename);
  public function is_uploaded_file($path);
  public function is_writable($filename);
  public function is_writeable($filename);
  public function lchgrp($filename, $group);
  public function lchown($filename, $owner);
  public function link($from_path, $to_path);
  public function linkinfo($path);
  public function lstat($filename);
  public function mkdir($pathname,
                        $mode = 0777,
                        $recursive = false,
                        $resource = null);
  public function move_uploaded_file($filename, $destination);
  public function parse_ini_file($filename,
                                 $process_sections = false,
                                 $scanner_mode = INI_SCANNER_NORMAL);
  public function parse_ini_string($ini,
                                   $process_sections = false,
                                   $scanner_mode = INI_SCANNER_NORMAL);
  public function pathinfo($path, $options);
  public function pclose($handle);
  public function popen($command, $mode);
  public function readfile($filename,
                           $use_include_path = false,
                           $context = null);
  public function readlink($path);
  public function realpath_cache_get();
  public function realpath_cache_size();
  public function realpath($path);
  public function rename($old_name, $new_name, $context = null);
  public function rewind($handle);
  public function rmdir($dirname, $context = null);
  public function set_file_buffer($stream, $buffer);
  public function stream_set_write_buffer($stream, $buffer);
  public function stat($filename);
  public function symlink($target, $link);
  public function tempnam($dir, $prefix);
  public function tmpfile();
  public function touch($filename, $time, $atime);
  public function umask($mask);
  public function unlink($filename, $context = null);
  public function scandir($directory,
                          $sorting_order = SCANDIR_SORT_ASCENDING,
                          $context = null);
}

?>