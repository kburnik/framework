<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface IViewProvider {
	
	function containsTemplate( $viewKey ) ;
	
	function getTemplate( $viewKey ) ;	
	
	function getView( $viewKey, $data );
	
}

?>