<?

class ImageProcessor {

	/////////////////////////////////////////////////////////////////////////////////////////
	// static context
	
	public static $imageTypes = array(
		// type // creation // output // quality
		  'gif' => array( 'gif' , 'imagecreatefromgif' , 'imagegif' )
		, 'jpeg' => array( 'jpeg' , 'imagecreatefromjpeg' , 'imagejpeg' , true )
		, 'jpg' => array( 'jpeg' , 'imagecreatefromjpeg' , 'imagejpeg'  , true )
		, 'png' => array( 'png' , 'imagecreatefrompng' , 'imagepng' )
	);
	
	public static function getExtension( $imageFilename ) {
		$parts = explode('.',$imageFilename);
		$extension = end( $parts );
		return $extension;
	}
	
	public static function GetImageType( $imageFilename ) {
		$extension = strtolower( self::GetExtension( $imageFilename ) );
		return self::$imageTypes[ $extension ][0];
	}
	
	public static function CreateImage( $imageFilename ) {
		$imageType = self::GetImageType( $imageFilename );
		$createFunction = self::$imageTypes[ $imageType ][ 1 ];		
		
		$image = call_user_func_array($createFunction,array($imageFilename));
		
		return $image;
	}
	
	public static function SaveImage( $sourceImage , $imageFilename , $quality = 100) {
		$imageType = self::GetImageType( $imageFilename );
		$saveFunction = self::$imageTypes[ $imageType ][ 2 ];
		$useQuality = isset(self::$imageTypes[ $imageType ][ 3 ]);
		
		$params = array( $sourceImage, $imageFilename );
		if ($useQuality) $params[] = $quality;
		
		return call_user_func_array( $saveFunction , $params );
	}
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	// object context
	
	private $sourceImage;
	private $imageFilename;
	private $quality;
	private $imageSize;
	
	
	public function __construct( $imageFilename = null , $quality = 80 ) {
		
		ini_set('memory_limit', '32M');		
		if ($imageFilename != null) {
			$this->imageFilename = $imageFilename;
			$this->sourceImage = self::CreateImage( $imageFilename );		
			$this->imageSize = getimagesize( $imageFilename );
			$this->quality = $quality;
		}
		
	}
	
	public function save( $imageFilename = null  , $quality = null ) {
		if ($imageFilename === null) $imageFilename = $this->imageFilename;
		if ($quality === null) $quality = $this->quality;
		
		return self::SaveImage( $this->sourceImage , $imageFilename , $quality);
	}
	
	
	public function getImageSize() {
		return $this->imageSize;
	}
	
	public function setQuality( $quality ) {
		$this->quality = $quality;
	}
	
	public function getQuality() {
		return $this->quality;
	}
	
	
	
	function restrictImage( $maxwidth = 800 ,$maxheight = 600 ) {

		list($width,$height)= $this->getImageSize();

		$src = $this->sourceImage;

		// all the same start points
		$dst_x = $dst_y = $src_x = $src_y = 0;
		
		$horizontal=($width>$height);
		$a=$width; $b=$height; // assume both dimensions to small 
		if ($horizontal) { 
			if ($width>$maxwidth) { // if it's too big  
				$aspect=$maxwidth/$width;
				$a=$maxwidth;
				$b=$aspect*$height;
			} 
			if ($b>$maxheight) { // if the height is  still too big
				$aspect=$maxheight/$height;
				$a=$aspect*$width;
				$b=$maxheight;
			}
		} else {
			list($maxwidth,$maxheight)=array($maxheight,$maxwidth);
			if ($height>$maxheight) { // if it's too big
				$aspect=$maxheight/$height;
				$a=$aspect*$width;
				$b=$maxheight;
			} 
			if ($a>$maxwidth) { // if the width is  still too big
				$aspect=$maxwidth/$width;
				$a=$maxwidth;
				$b=$aspect*$height;
			}
		}
		
		$tmp = imagecreatetruecolor($a,$b);
		imagecopyresampled($tmp,$src,$dst_x,$dst_y,$src_x,$src_y,$a,$b,$width,$height);
		
		imagedestroy($src);
		
		// update the new source
		$this->sourceImage = $tmp;
		
		// update the new size
		$this->imageSize = array( $a , $b);
		
		// chain
		return $this;
		
	}
	
	
	function enboxImage($newwidth=800, $newheight=600) {
						
		$src = $this->sourceImage;

		list($width,$height)= $this->getImageSize();		
		
		$tmp=imagecreatetruecolor($newwidth,$newheight);
		
		sscanf($bgcolor,"%d,%d,%d",$r,$g,$b);
		$white = imagecolorallocatealpha($tmp,$r,$g,$b,0);
		imagefill($tmp, 0, 0, $white);
		
		$a=$width; $b=$height;
		
		$aspect=$width/$height;
		$newaspect=$newwidth/$newheight;
		if ($aspect<$newaspect) {
			$src_w=$width;
			$src_h=round(($width/$newwidth)*$newheight);
		} else {
			$src_h=$height;
			$src_w=round(($height/$newheight)*$newwidth);
		}

		$src_x=($width-$src_w)/2;
		$src_y=($height-$src_h)/2;
		
		$dst_x=0;
		$dst_y=0;
		$dst_w=$newwidth;
		$dst_h=$newheight;

		imagecopyresampled($tmp,$src,$dst_x,$dst_y,$src_x,$src_y,$dst_w,$dst_h,$src_w,$src_h);
	
		
		imagedestroy($src);
		
		// update the new source
		$this->sourceImage = $tmp;
		
		// update the new size
		$this->imageSize = array( $newwidth , $newheight);
		
		// chain
		return $this;		

	}  

	
	function enboxImagePreserve($newwidth=800, $newheight=600, $bgcolor="255,255,255" ) {

		$src = $this->sourceImage;

		list($width,$height)= $this->getImageSize();
		
		$tmp = imagecreatetruecolor($newwidth,$newheight);
		
		
		sscanf($bgcolor,"%d,%d,%d",$r,$g,$b);
		$white = imagecolorallocatealpha($tmp,$r,$g,$b,0);
		imagefill($tmp, 0, 0, $white);
		
		$a=$width; $b=$height;
		
		$nottosmall=true;
		if ($width>$height) {
			if ($newwidth>$width) $nottosmall=false;
		} else {
			if ($newheight>$width) $nottosmall=false;
		}
			
		if ($a>$newwidth) {
				$b=($newwidth/$a)*$b;
				$a=$newwidth;
		} 
		if ($b>$newheight) {
			$a=$newheight/$b*$a;
			$b=$newheight;
		}
		$src_x=0;
		$src_y=0;
		$dst_x=($newwidth-$a)/2;
		$dst_y=($newheight-$b)/2;
		imagecopyresampled($tmp,$src,$dst_x,$dst_y,$src_x,$src_y,$a,$b,$width,$height);


		// destroy the old source
		imagedestroy($src);
		
		// update the new source
		$this->sourceImage = $tmp;
		
		// update the new size
		$this->imageSize = array( $newwidth , $newheight);
		
		// chain
		return $this;
	}
	
	
	function applyWatermark( $imageFilename ) {
		// Load he stamp and the photo to apply the watermark to
		
		$stamp = self::CreateImage( $imageFilename );


		// Set the margins for the stamp and get the height/width of the stamp image
		$marge_right = 0;
		$marge_bottom = 0;
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
		
		// Copy the stamp image onto our photo using the margin offsets and the photo 
		// width to calculate positioning of the stamp. 
		
		imagecopy($this->sourceImage, $stamp, imagesx($this->sourceImage) - $sx - $marge_right, imagesy($this->sourceImage) - $sy - $marge_bottom, 0, 0, $sx, $sy);
		
		imagedestroy($stamp);
		
		// chain
		return $this;
		
	}
	
	
	public function rotateImage( $clockwise ) {
	
	
		// determine degrees from direction
		$degrees = (!$clockwise) ? 90 : 270;
		
		// rotate the image
		$this->sourceImage = imagerotate($this->sourceImage, $degrees, 0);
		
		// update the size 
		$this->imageSize = array( imagesx($this->sourceImage) , imagesy($this->sourceImage) );
		
		// chain
		return $this;
	}
		
	
	
		
	
	
	private $destroyed = false;
	// destroy the image in memory
	public function destroy() {
		if ($this->sourceImage != null) 
			imagedestroy($this->sourceImage);
		$this->destroyed = true;
	}
	
	public function __destruct() {
		if (!$this->destroyed) {
			$this->destroy();
		}
	}
		

}


?>