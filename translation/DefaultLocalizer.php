<?php

// Default localizer. Useful for most purposes of content localization.
class DefaultLocalizer implements ILocalizer {
  private $locale;
  private $fileSystem;

  public function __construct($locale = "en", $fileSystem = null) {
    $this->locale = $locale;
    if ($fileSystem == null) {
      $fileSystem = new FileSystem();
    }
    $this->fileSystem = $fileSystem;
  }

  public function setLocale($locale) {
    return $this->locale;
  }

  public function getLocale() {
    return $this->locale;
  }

  // @implements ILocalizer.
  public function readFile($filename) {
    return file_get_contents($this->findLocalizedFile($filename));
  }

  // Attempts to find a localized version of the file in the same location.
  // Returns the provided filename if a localized version is not found.
  private function getLocalizedFilename($filename) {
    $basename = $this->fileSystem->basename($filename);
    $parts = explode(".", $basename);
    $locale = $this->getLocale();

    if (count($parts) > 0) {
      $ext = array_pop($parts);
      $parts[] = $locale;
      $parts[] = $ext;
    } else {
      $parts[] = $locale;
    }

    $basename = implode(".", $parts);
    $localizedFilename =
        $this->fileSystem->dirname($filename) . "/" . $basename;

    if ($this->fileSystem->file_exists($localizedFilename)) {
      return $localizedFilename;
    } else {
      return $filename;
    }
  }
}
