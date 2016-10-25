<?php

class ShellUtil
{

  // default parameters
  private $parameters = array(
    'h' => array('help','Displays this list of options')
    /*
      'r:' => 'required:',
      'o::' => 'optional::',
        */
  );

  public function setParameters( $parameters )
  {
    $this->parameters = array_merge($this->parameters , $parameters );

    return $this;
  }


  private function parseOptions( $argv )
  {

    $parameters = $this->parameters;

    $optParams = $this->toOptParams( $parameters );

    $options = getopt(implode('', array_keys($optParams)), $optParams);
    $pruneargv = array();
    $namedParams = $this->toNamedParams( $parameters );


    foreach ($options as $option => $value)
    {
      foreach ($argv as $key => $chunk) {
      $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
      if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
        array_push($pruneargv, $key);
      }
      }
      if (array_key_exists($option , $namedParams))
      {
      $options[ $namedParams[$option] ] = $value;
      unset( $options[ $option ] );
      }
    }
    while ($key = array_pop($pruneargv)) unset($argv[$key]);
    $argv = array_values( $argv );

    return $options;
  }


  private function toNamedParams( $parameters )
  {
    $out = array();
    foreach ( $parameters as $short => $descriptor )
    {
      list($long,$desc) = $descriptor;
      $out [ str_replace(':','',$short) ] = str_replace(':','',$long);
    }
    return $out;
  }

  private function toOptParams( $parameters )
  {
    $out = array();
    foreach ( $parameters as $short => $descriptor )
    {
      $out[ $short ] = reset( $descriptor );
    }
    return $out;
  }

  public function runOptions( $argv )
  {
    $options = $this->parseOptions( $argv );

    foreach ( $options as $option => $value )
    {
      $funcName = "option".ucfirst( $option );

      // print_R( "$funcName $value\n" );

      if ( method_exists( $this , $funcName  ) )
      {
        $this->$funcName( $value );
      }



    }

    return $this;

  }

  public function optionHelp()
  {
    foreach ( $this->parameters as $short => $descriptor )
    {
      echo " -{$short}, --{$descriptor[0]}\n\t{$descriptor[1]}\n\n";
    }
  }
}

