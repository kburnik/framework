<?php

// Default localizer. Useful for most purposes of content localization.
class DefaultLocalizer implements ILocalizer {
  private $locale;
  private $fileSystem;
  private $langTable;

  public function __construct($locale = "en-us", $fileSystem = null) {
    $this->locale = $locale;
    if ($fileSystem == null) {
      $fileSystem = new FileSystem();
    }
    $this->fileSystem = $fileSystem;
  }

  // @implements ILocalizer.
  public function selectLocale($default_locale="en-us",
                               $candidates=array(),
                               $supported_locales=array("en-us")) {
    $locale = $default_locale;
    foreach ($candidates as $candidate) {
      if (in_array($candidate, $supported_locales)) {
        $locale = $candidate;
        break;
      }
    }

    $this->setLocale($locale);
  }

  public function setLocale($locale) {
    $this->locale = $locale;
  }

  public function getLocale() {
    return $this->locale;
  }

  public function setLangTable($langTable) {
    $this->langTable = $langTable;
  }

  public function getLangTable() {
    if ($this->langTable == null) {
      $this->langTable = $this->loadLangTableForLocale($this->getLocale());
    }
    return $this->langTable;
  }

  // @implements ILocalizer.
  public function readFile($filename) {
    return $this->translate(
        $this->fileSystem->file_get_contents(
          $this->getLocalizedFilename($filename)));
  }

  // Translates a template content using the current locale.
  private function translate($content) {
    $translator =
        new Translator($content, $this->getLangTable());
    return $translator->translate($this->getLocale());
  }

  // Loads up a language table from a locale file (e.g.
  // PROJECTROOT/localization/en.json). Operation is cached during the
  // localizer object life time.
  private function loadLangTableForLocale($locale) {
    static $cache = array();
    if (array_key_exists($locale, $cache)) {
      return $cache[$locale];
    }

    $localeFile = Project::GetProjectDir('/localization/' . $locale . ".json");
    if (!$this->fileSystem->file_exists($localeFile)) {
      return $cache[$locale] = array();
    }

    $rawData = $this->fileSystem->file_get_contents($localeFile);

    $localeData = json_decode($rawData, true);
    if (json_last_error() != 0) {
      throw new Exception("Failed decoding locale file: $localeFile");
    }

    foreach ($localeData as $key => $value) {
      $localeData[$key] = array($locale => $value);
    }

    $cache[$locale] = $localeData;
    return $localeData;
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
