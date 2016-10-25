<?php

class FileViewProvider extends ViewProvider {

  protected $map = array();
  protected $filesystem;

  public function __construct($map = array(), IFileSystem $filesystem = null) {
    $this->map = $map;
    if ($filesystem == null)
      $filesystem = new FileSystem();

    $this->filesystem = $filesystem;
  }

  public function getTemplate($viewKey) {
    $filename = $this->map[ $viewKey ];

    // fallback to default view location
    if ( !$this->filesystem->file_exists($filename) )
    {
      $filename = view($filename);
    }

    if ( ! $this->filesystem->file_exists( $filename ) )
    {
      throw new Exception("Missing view file: $filename");
    }

    if (($template =
          $this->filesystem->file_get_contents( $filename )) !== false) {
      return $template;
    }
    else
    {
      throw new Exception("Cannot open view file: $filename");
    }

  }

  public function containsTemplate( $viewKey ) {
    return array_key_exists( $viewKey , $this->map );
  }


}

