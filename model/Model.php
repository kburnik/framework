<?

/*
	Model:
		- extends the BaseModel
		- abstractly provides resources (JS and CSS) for pages using the model (with extra implementation)
		- abstractly provides an URL for a model item (i.e. entity)

*/

/**
 * Abstract Model class:
 * - extend this class whenever creating a Model which uses a Queried Data Provider 
 * @author Kristijan Burnik
 *
 */
abstract class Model extends BaseModel {

	protected $qdp;
		
	
	/**
	 * when implemented in derived class, returns the lists of JS and CSS resources
	 * which can be placed to a page, typically those used by the model on the client side
	 * returns an array in following format
	 * array(
			  'css' => array( 'cssfile1.css' , 'cssfile2.css' , )
			, 'js' => array( 'jsfile1.css' , 'jsfile2.css' ,  )
		)
	 */
	abstract function getResources();
	
	
	/**
	 * When implemented in derived class, returns an URL corresponding to a Model entity or item
	 * @param mixed $item
	 */
	abstract function getURL($item);
	
	
	function __construct($queryDataProvider = null) {
	
		// call BaseModel::__construct
		parent::__construct( $queryDataProvider );
		
		$useLog = !defined('SKIP_MODEL_LOGGING');
		
				
		$resources = $this->getResources();		
		if ($useLog)
			Console::WriteLine('Model :: including resources ' . var_export($resources,true));
		
		Project::getCurrent()->includeResources($resources);
		
		if ($useLog)
			Console::WriteLine('Model :: Binding model\'s auto event handlers for ' . get_class($this) );
			
		Project::getCurrent()->bindProjectAutoEventHandlers( $this );
		
	}
	
}


?>