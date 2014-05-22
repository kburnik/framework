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


		foreach ( $this->queue as $descriptor )
		{
			list( $entityClassName , $structure, $indices ) = $descriptor;



			$structure = array_merge( $structure, $indices );

			$qdp->drop( $entityClassName );

			$query = $qdp->prepareTableQuery( $entityClassName , $structure , "INNODB" );

			$qdp->prepareTable( $entityClassName , $structure , "INNODB" );

			if ($err = $qdp->getError() ) {
				echo $err."\n";
				echo $query."\n";
			}
		}


		foreach ( $this->queue as $descriptor )
		{

			list( $entityClassName , $structure, $indices ) = $descriptor;

			$items = $entityClassName::All(array())->extract();

			if ( !($c = count($items)) )
				continue;


			echo "Inserting $entityClassName ($c): ";

			$last_perc = -1;
			$perc = -1;
			foreach ($items as $i=>$item )
			{
				$last_perc = $perc;
				$perc = intval($i * 100 / $c);

				if ( ($perc > $last_perc) && ($perc%2 == 0) )
					echo ".";

				$qdp->insert( $entityClassName , $item );

				if ($err = $qdp->getError() )
				{
					echo $err."\n";
					echo substr($qdp->last_query,400)." ...\n";
				}
			}

			echo "\n";

		}
		$qdp->execute("SET FOREIGN_KEY_CHECKS=1;");

	}

}

?>