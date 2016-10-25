<?php


abstract class XMLFileViewProviderFactory  implements IViewProviderFactory
{
  protected $viewProviderMap;

  abstract protected function generateFilename( $viewProviderKey );


  public function __construct( $viewProviderMap )
  {
    $this->viewProviderMap = $viewProviderMap;

  }


  public function viewProviderExists( $viewProviderKey )
  {
    return array_key_exists( $viewProviderKey , $this->viewProviderMap );
  }

  public function getViewProvider( $viewProviderKey )
  {

    $xmlFile =$this->generateFilename( $viewProviderKey );

    $storage = new XMLFileStorage( $xmlFile );

    $viewProvider = new StorageViewProvider( $storage );

    return $viewProvider;
  }
}

