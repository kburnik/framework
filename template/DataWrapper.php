<?

class DataWrapper {

	public static function GroupByThree( $data ) {
		$rows = array();
		
		$rowindex = 0;
		$counter = 0;
		foreach ($data as $key => $value) {
			
			$rows[ $rowindex ][ $key ] = $value;
		
			$counter++;
			if ($counter > 2) {				
				$rowindex++;
				$counter = 0;
			}
			
		}
		
		return $rows;
	
	}


}



?>