<?

class FileSystem implements IFileSystem
{

	public function basename( $path, $suffix = null ){ return basename( $path, $suffix ); }
	
	public function chdir( $directory ) { return chdir( $directory );  }
	
	public function chgrp( $filename, $group ){ return chgrp( $filename , $group ); }	
	
	public function chown( $filename, $user ){ return chown( $filename , $user ); }
	
	public function chmod( $filename, $mode ){ return chmod( $filename , $mode ); }
	
	public function clearstatcache( $clear_realpath_cache = false ,  $filename = null) { return clearstatcache( $clear_realpath_cache , $filename ); }
	
	public function copy( $source, $dest, $context = null ){ return copy( $source , $dest ); }
	
	public function delete( $filename, $context = null ){ return delete( $filename ); }
	
	public function dirname( $path ){ return dirname( $path ); }
	
	public function disk_free_space( $directory ){ return disk_free_space( $directory ); }
	
	public function disk_total_space ( $directory ){ return disk_total_space( $directory ); }
	
	public function diskfreespace( $directory ){ return disk_free_space( $directory ); }
	
	public function fclose( $handle ){ return fclose( $handle ); }
	
	public function feof( $handle ){ return feof( $handle ); }
	
	public function fflush( $handle ){ return fflush( $handle );  }
	
	public function fgetc( $handle ){ return fgetc( $handle ); }
	
	public function fgets( $handle, $length = 0 ){ return fgets( $handle, $length ); }
	
	public function fgetss( $handle, $length = 0, $allowed_tags = array() ){ return fgetss( $handle , $length, $allowed_tags ); }
	
	public function file_exists( $filename ){ return file_exists( $filename ); }
	
	public function file_get_contents($filename, $flags=0, $context=null, $offset = 0, $maxlen = 10000000 ){ return file_get_contents( $filename , $flags, $context, $offset, $maxlen ); }
	
	public function file_put_contents($file, $data, $flags = 0, $context = null){ return file_put_contents( $file, $data, $flags, $context ); }
		
	public function file($filename,$flags=0,$context = null){ return file( $filename , $flags, $context ); }
	
	public function fileatime( $filename ){ return fileatime( $filename ); }
	
	public function filectime( $filename ){ return filectime( $filename ); }
	
	public function filegroup( $filename ){ return filegroup( $filename ); }
	
	public function fileinode( $filename ){ return fileinode( $filename ); }
	
	public function filemtime( $filename ){ return filemtime( $filename ); }
		
	public function fileowner( $filename ){ return fileowner( $filename ); }
	
	public function fileperms( $filename ){ return fileperms( $filename ); }
	
	public function filesize( $filename ){ return filesize( $filename ); }
	
	public function filetype( $filename ){ return filetype( $filename ); }
	
	public function flock( $handle , $operation , & $wouldblock ){ return flock( $handle, $operation, $wouldblock ); }
	
	public function fnmatch( $pattern, $filename, $flags = 0 ){ return fnmatch( $pattern, $filename , $flags ); }
	
	public function fopen( $filename, $mode, $use_include_path = false, $context = null ){ return fopen( $filename, $mode, $use_include_path, $context ); }
	
	public function fpassthru( $handle ){ return fpassthru( $handle ); }
	
	public function fputcsv( $handle, $fields, $delimiter=",", $enclosure = '"' ){ return fputcsv( $handle, $fields , $delimiter, $enclosure ); }
	
	public function fputs( $handle, $string, $length = 0 ){ return fputs( $handle , $string, $length ); }
	
	public function fread( $handle, $length ){ return fread( $handle , $length ); }
	
	public function fscanf( $handle, $format ){ return fscanf( $handle , $format ); }
	
	public function fseek( $handle, $offset, $whence = SEEK_SET ){ return fseek( $handle, $offset, $whence ); }
	
	public function fstat( $handle ){ return fstat( $handle ); }
	
	public function ftell( $handle ){ return ftell( $handle ); }
	
	public function ftruncate( $handle , $size ){ return ftruncate( $handle, $size ); }
	
	public function fwrite( $handle, $string, $length = 0){ return fwrite( $handle, $string , $length ); }
	
	public function getcwd() { return getcwd(); }
	
	public function glob( $pattern, $flags = 0 ){ return glob( $pattern , $flags ); }	
	
	public function is_dir( $filename ){ return is_dir( $filename ); }
	
	public function is_executable( $filename ){ return is_executable( $filename ); }
	
	public function is_file( $filename ){ return is_file( $filename ); }
	
	public function is_link( $filename ){ return is_link( $filename ); }
	
	public function is_readable( $filename ){ return is_readable( $filename ); }
	
	public function is_uploaded_file( $path ){ return is_uploaded_file( $path ); }
	
	public function is_writable( $filename ){ return is_writable( $filename ); }
	
	public function is_writeable( $filename ){ return is_writable( $filename ); }
	
	public function lchgrp( $filename, $group ){ return lchgrp( $filename, $group ); }
	
	public function lchown( $filename, $owner ){ return lchown( $filename , $owner ); }
	
	public function link( $from_path, $to_path ){ return link( $from_path , $to_path ); }
	
	public function linkinfo( $path ){ return linkinfo( $path ); }
	
	public function lstat( $filename ){ return lstat( $filename ); }
	
	public function mkdir( $pathname , $mode = 0777 , $recursive = false , $resource = null ){ return mkdir( $pathname , $mode , $recursive ); }
	
	public function move_uploaded_file( $filename , $destination ){ return move_uploaded_file( $filename , $destination ); }
	
	public function parse_ini_file( $filename, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL ){ return parse_ini_file( $filename , $process_sections , $scanner_mode ); }
	
	public function parse_ini_string( $ini, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL ){ return parse_ini_string( $ini, $process_sections , $scanner_mode ); }
	
	public function pathinfo( $path, $options ){ return pathinfo( $path, $options ); }
	
	public function pclose( $handle ){ return pclose( $handle ); }
	
	public function popen( $command , $mode ){ return popen( $command , $mode ); }
	
	public function readfile( $filename, $use_include_path = false, $context = null ){ return readfile( $filename , $use_include_path, $context	); }
	
	public function readlink( $path ){ return ( $path ); }
	
	public function realpath_cache_get(){ return realpath_cache_get( ); }
	
	public function realpath_cache_size(){ return realpath_cache_size( ); }
	
	public function realpath( $path ){ return realpath( $path ); }
	
	public function rename( $old_name, $new_name, $context = null ){ return rename( $old_name , $new_name ); }
	
	public function rewind( $handle ){ return rewind( $handle ); }
	
	public function rmdir( $dirname, $context = null ){ return rmdir( $dirname , $context ); }
	
	public function set_file_buffer( $stream , $buffer ){ return stream_set_write_buffer( $stream, $buffer ); }
	
	public function stream_set_write_buffer( $stream , $buffer ){ return stream_set_write_buffer( $stream, $buffer ); }
	
	public function stat( $filename ){ return stat( $filename ); }
	
	public function symlink( $target , $link ){ return symlink( $target, $link ); }
	
	public function tempnam( $dir , $prefix ){ return tempnam( $dir, $prefix ); }
	
	public function tmpfile(){ return tmpfile( ); }
	
	public function touch( $filename, $time, $atime){ return touch( $filename , $time, $atime ); }
	
	public function umask( $mask ){ return umask( $mask ); }
	
	public function unlink($filename, $context = null){ return unlink( $filename , $context ); }


}

?>