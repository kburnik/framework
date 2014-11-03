<?

class EntityBuilder extends EntityCrawler
{

	private $stats;

	private $dataDriver;

	private $queue;

	private function getQDP()
	{
		return Project::GetQDP();
	}

	protected function handleEntity( $sourceEntry , $entityName )
	{

		$entityClassName = $entityName;

		$qdp = $this->getQDP();

		$dd = $this->dataDriver;

		$er = new EntityReflection( "$entityClassName" , $dd );




		$structure = $er->getStructure();

		$indices = $er->getIndices();


		if ( $structure )
		{

			if ( count($argv)>1 && $entityClassName != $argv[1] )
				continue;

			$entityModelClassName = "{$entityClassName}Model";

			$model = $entityModelClassName::getInstance();

			$entityClassName = strtolower( $entityClassName );

			$this->queue[] = array( $entityClassName , $structure , $indices )  ;


		} else {
			$res = "Error";
		}

		$this->stats[ $entityClassName ] = array($res,$indices);

	}

	public function build( $sourceEntry , $dataDriver = null )
	{

		flush();
		ob_flush();
		ob_end_flush();

		$this->resolveProject( $sourceEntry );

		if ( $dataDriver === null )
			$dataDriver = new MySQLDataDriver();

		$this->dataDriver = $dataDriver;

		$this->traverse( $sourceEntry );


		$qdp = $this->getQDP();

		$qdp->execute("SET FOREIGN_KEY_CHECKS=0;");

		$tables = $qdp->getTables();

		$backup_exists = array();

		foreach ( $this->queue as $descriptor )
		{
			list( $entityClassName , $structure, $indices ) = $descriptor;

			$structure = array_merge( $structure, $indices );

			if ( in_array( $entityClassName , $tables ) )
			{
				$backup_exists[ $entityClassName ] = true;
				$qdp->execute("create table `backup_{$entityClassName}` like `{$entityClassName}`");
				$qdp->execute("insert into `backup_{$entityClassName}` ( select * from `{$entityClassName}` );");
				$aff = $qdp->getAffectedRowCount();
				$qdp->drop( $entityClassName );
				print_r(  "Backed up rows for $entityClassName: " . $aff . "\n");
			} else {
				echo "Table $entityClassName does not yet exist\n";
			}


			

			$query = $qdp->prepareTableQuery( $entityClassName , $structure , "INNODB" );

			$qdp->prepareTable( $entityClassName , $structure , "INNODB" );

			echo "Created table $entityClassName\n";
			
			if ($err = $qdp->getError() ) {
				echo $err."\n";
				echo $query."\n";
			}
		}


		foreach ( $this->queue as $descriptor )
		{

			list( $entityClassName , $structure, $indices ) = $descriptor;
			
			if ( ! $backup_exists[ $entityClassName ] )
				continue;
			
			$oldFields = $qdp->getFields("backup_{$entityClassName}");
			$oldFields = "`".implode("`,`",$oldFields)."`";

			$qdp->execute("
					insert into `{$entityClassName}` ( {$oldFields} )
					( select {$oldFields} from `backup_{$entityClassName}` ) ;
			");
			$aff = $qdp->getAffectedRowCount();
			echo "Restored rows for new structure of $entityClassName: $aff\n";
			

			if ($err = $qdp->getError() )
			{
				echo $err."\n";
				echo substr($qdp->last_query,400)." ...\n";
			} else {
				$qdp->drop("backup_{$entityClassName}");
			}

			echo "\n";

		}
		$qdp->execute("SET FOREIGN_KEY_CHECKS=1;");

	}

}

?>