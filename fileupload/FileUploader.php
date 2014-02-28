<?

abstract class FileUploader implements IFileUploader {
	
	private static $errorCodeMap = array(
	      0 => 'unknown_error'
		, 1 => 'exceeds_upload_max_filesize'
		, 2 => 'exceeds_max_file_size'
		, 3 => 'partial_upload'
		, 4 => 'no_file_uploaded'
		, 5 => 'missing_temporary_directory'
		, 6 => 'disk_write_fail'
		, 7 => 'stopped_by_extension'		
		, 100 => 'nothing_uploaded'		
		, 200 => 'unsupported_filetype'
		, 300 => 'failed_moving_file'
		, 400 => 'failed_creating_path'
		, 500 => 'not_uploaded_file'
		, 600 => 'non_existing_temporary_file'
		, 1000 => 'not_authenticated'
	);
	
	// users must be authenticated to perform uploads
	protected abstract function isAuthenticated() ;
	
	
	public function __construct() {	
	
		$success = true;
		foreach ( $_FILES as $i => $file ) {
			$ok = $this->handleFile( $file );
			if (!$ok) {
				$success = false;
			}
			// removing files
			unset($_FILES[$i]);
		}
		
		if ( $success ) {
			$this->onRequestSuccess();
		} else {
			$this->onRequestError();
		}
		
	}
	
	private function handleFile( $file ) {
		if (!$this->isAuthenticated()) {
			// not authenticated
			$errorCode = 1000;
		} else if( ! empty( $file['error'] ) ) {
			$errorCode = $file['error'];
			if ( ! array_key_exists( $errorCode , self::$errorCodeMap ) ) {
				// unknown_error
				$errorCode = 0;		
			};			
		} else if ( empty($file['tmp_name']) || $file['tmp_name'] == 'none' ) {
			// nothing_uploaded
			$errorCode = 100; 
				
		}  else if (!in_array( $file['type'], $this->getAllowedFileTypes() )) {
			// unsupported_filetype
			$errorCode = 200; 
		} else {
			
			$this->onFileUploadSuccess( $file );
			return true;
		}
		
		
		$this->onFileUploadError( $file, $errorCode , self::$errorCodeMap[ $errorCode ]  );
		return false;
	}
	
	private $movedFiles = array();
	protected function moveUploadedFile( $file , $destination , $digThrough = false, $permissions = 0755) {
		$errorCode = null;
		
		if ( $digThrough ) {
			$destinationDirectory = dirname( $destination );
			
			if ( !file_exists( $destinationDirectory ) ) {
				// create directory recursively
				if ( mkdir( $destinationDirectory , $permissions , true ) ) {
					// all is ok so far
					
				} else {
					$errorCode = 400;
				}
			}
		
		}
		
		if ( $errorCode === null ) {
			if (file_exists($file['tmp_name'])) {
				if (is_uploaded_file($file['tmp_name'])) {
					if ( move_uploaded_file( $file['tmp_name'], $destination) ) {
						
						$this->movedFiles[] = $destination;
						return true;
					} else {
						// failed moving file
						$errorCode = 300;
					}
				} else {
					// not an uploaded file
					$errorCode = 500;					
				}
			} else {
				// non existing temporary file
				$errorCode = 600;
			}
		}
		
		$details = array( $destination , $digThrough , $permissions );
		$this->onFileUploadError( $file, $errorCode , self::$errorCodeMap[ $errorCode ] , $details );
		return false;
	
	}
	
	public function generateUniqueBasename( $file ) {
		if (!is_array($file)) {
			$filename = $file;
		} else {
			$filename = $file['name'];
		}
		$extension = end(explode('.',basename($filename)));
		$uniqueName = date('YmdHis').'000'.rand(10000,99999) . '.' . $extension;
		return $uniqueName;
		
	}
	
	public function getMovedFiles() {
		return $this->movedFiles;
	}
		

}





?>