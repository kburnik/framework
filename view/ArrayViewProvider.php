<?
include_once(dirname(__FILE__)."/../base/Base.php");

class ArrayViewProvider extends ViewProvider {

  private $templates = null;
  function __construct( $templates ) {
    if ( ! is_array( $templates ) ) {
      throw new Exception("ArrayViewProvider :: No array provided for views, got ". var_export( $templates, true ));
    }
    $this->templates = $templates;
  }

  function getTemplate( $viewKey ) {
    return $this->templates[ $viewKey ];
  }

  function containsTemplate( $viewKey ) {
    return  array_key_exists(  $viewKey , $this->templates ) ;
  }
  //

}
?>