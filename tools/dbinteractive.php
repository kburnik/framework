#!/usr/bin/env php
<?

include_once( dirname(__FILE__)."/.tools.php" );

echo colored( "-> " , "green" );

$mysql = Project::GetQDP();

while( $line = readline() )
{


  $line = trim( $line );
  $code .= "\r\n" . $line;

  if ( strlen($line) == 0 || substr($code,-1) == ";" )
  {
  $res = $mysql->execute( $code )->toArray();

  $err = $mysql->getError();

  if ($err) {
    echo colored( "Error\n" , "red" );
    echo colored( $err, "white" );
  }
  echo colored( json_format(json_encode($res)) . "\n" , "yellow" );
  echo colored( "Affected: " . $mysql->getAffectedRowCount() , "yellow" );
  $code = "";
  echo "\n---\n\n";
  }

   echo colored( "-> " , "green" );
}

?>