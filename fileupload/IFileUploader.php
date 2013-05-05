<?

interface IFileUploader {

	/*
	// handle single file
	function handleFile( $file );
	
	// move the uploaded file
	function moveUploadedFile( $file , $destination , $digThrough = false , $permissions = '0755' ) ;
	
	// get the list of all uploaded and moved files
	function getMovedFiles() ;
	
	// generate a unique basename for file
	function generateUniqueBasename( $file ) ;
	
	*/
	

	// get list of supported mime types
	public function getAllowedFileTypes () ; 
	
	/*
	// single file upload success
	function onFileUploadSuccess ( $file );
	
	// single file upload error
	protected function onFileUploadError( $file, $errorCode , $errorTag , $details = null ) ;	
	*/
	
	/*
	// whole request succeded
	protected function onRequestSuccess();
	
	// part of request failed
	protected function onRequestError();
	*/
	
}

?>