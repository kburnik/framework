<?

class Async {

  public static function IncludeScript( $php_script_path , $description ) {
    $path = dirname(__FILE__);
    return exec("{$path}/callscript.sh {$php_script_path} ".escapeshellarg($description)." > /dev/null 2>&1 &");
  }

  public static function CallModelFunction( $function , $paramsArray , $description = null  ) {

    $temp_file = exec("mktemp");

    chmod($temp_file,0755);

    $permissions = substr(sprintf('%o', fileperms($temp_file)), -4);

    if ($permissions != '0755') {
      throw new Exception('Async::call_user_func_array : Could not CHMOD temporary file!');
    }

    $project_file = Project::GetProjectFile();

    if (count($function) == 2 && is_string($function[0]) && is_string($function[1])  ) {

      $function = array( $function[0] . "::getInstance()" , var_export( $function[1] , true ) );
    } else {
      throw new Exception("Async::call_user_func_array  : function can only be a model function of type array( 'ModelClass' , 'publicFunctionName' )");
    }

    try {
      $paramsArray_exported = var_export($paramsArray,true);
    } catch (Exception $ex) {
      throw new Exception("Async::call_user_func_array  : Could not serialize async params ",0,$ex);
    }

    $code = "<?
      define('__ASYNCHRONOUS_MODE__',true);
      include('{$project_file}');
      call_user_func_array(array({$function[0]},{$function[1]}),$paramsArray_exported);
    ?>";

    $written_bytes = file_put_contents($temp_file,$code);

    $pid = "PHP " . getmypid();

    if ($written_bytes) {

      if ($description === null) {
        $description = json_encode($function);
      }

      self::includeScript( $temp_file , $description );


    } else {

      $date = date("Y-m-d H:i:s") . " " . intval(microtime(true)*1000);
      file_put_contents(dirname(__FILE__).'/async.log.txt',"$pid $date :: Async::call_user_func_array : Couldn't write to temporary file (PHP)!\n",FILE_APPEND);

      throw new Exception("Async::call_user_func_array : Couldn't write to temporary file!");
    }

    return true;

  }


}


?>