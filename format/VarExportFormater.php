<?

class VarExportFormater implements IOutputFormater {
  
  function Initialize() {
  
  }
  
  function Format($data) {
    return var_export($data,true);
  }
  
}
?>