<?php

// Default localizer. Useful for most purposes of content localization.
class DefaultLocalizer implements ILocalizer {
  private $default_locale;
  private $fileSystem;
  private $langTable;
  private $current_locale;
  private $supported_locales;

  public function __construct($default_locale = "en-us",
                              $supported_locales = array("en-us"),
                              $fileSystem = null) {
    $this->current_locale = $default_locale;
    $this->default_locale = $default_locale;
    $this->supported_locales = $supported_locales;
    if ($fileSystem == null) {
      $fileSystem = new FileSystem();
    }
    $this->fileSystem = $fileSystem;
  }

  // @implements ILocalizer.
  public function selectCurrentLocale($candidates=array()) {
    $locale = $this->default_locale;
    foreach ($candidates as $candidate) {
      if (in_array($candidate, $this->getSupportedLocales())) {
        $locale = $candidate;
        break;
      }
    }

    $this->setCurrentLocale($locale);
  }

  public function setCurrentLocale($locale) {
    $this->current_locale = $locale;
  }

  // @implements ILocalizer.
  public function getSupportedLocales() {
    return $this->supported_locales;
  }

  // @implements ILocalizer.
  public function getDefaultLocale() {
    return $this->default_locale;
  }

  // @implements ILocalizer.
  public function getCurrentLocale() {
    return $this->current_locale;
  }

  // @implements ILocalizer.
  public function readFile($filename) {
    if (!$this->fileSystem->file_exists($filename))  {
      throw new Exception("Template file does not exist: $filename");
    }

    $locale = $this->getCurrentLocale();
    $template_mtime = $this->fileSystem->filemtime($filename);
    $translation_mtime = 0;
    $tr_table_mtime = 0;

    $translation_filename =
        self::getTranslationFilename($filename,
                                     $locale,
                                     $this->fileSystem);
    if ($this->fileSystem->file_exists($translation_filename)) {
      $translation_mtime = $this->fileSystem->filemtime($translation_filename);

      $tr_table_filename = self::getTranslationTableFilename($filename,
                                                             $locale);
      if ($this->fileSystem->file_exists($tr_table_filename)) {
        $tr_table_mtime = $this->fileSystem->filemtime($tr_table_filename);
      }

      // Read the cached version if nothing changed in the sources.
      if ($template_mtime < $translation_mtime &&
          $tr_table_mtime < $translation_mtime) {
        return $this->fileSystem->file_get_contents($translation_filename);
      }
    }

    $translation_table = $this->getTranslationTable($filename, $locale);
    $template = $this->fileSystem->file_get_contents($filename);
    $mapper = new LocaleMapper($template, $translation_table);
    $localized_template = $mapper->apply();

    // Cache the translation.
    if ($template_mtime > $translation_mtime ||
        $tr_table_mtime > $translation_mtime) {
      $this->fileSystem->file_put_contents($translation_filename,
                                           $localized_template);
    }

    return $localized_template;
  }

  // Rebuilds the translation tables for each supported locale in the provided
  // localizer targeting the template file.
  public static function rebuildTranslationTables($filename,
                                                  $localizer,
                                                  $fileSystem) {
    $locales = $localizer->getSupportedLocales();
    $template = $fileSystem->file_get_contents($filename);
    foreach ($locales as $locale) {
      $translation_table = $localizer->getTranslationTable($filename, $locale);
      $mapper = new LocaleMapper($template, $translation_table);
      if ($mapper->update()) {
        $translation_table = $mapper->getTranslationTable();
        self::saveTranslationTable($filename,
                                   $locale,
                                   $translation_table,
                                   $fileSystem);
      }
    }
  }

  // Stores the translation table for the given template file and locale.
  private static function saveTranslationTable($filename,
                                               $locale,
                                               $translation_table,
                                               $fileSystem) {
    $tr_table_filename = self::getTranslationTableFilename($filename,
                                                           $locale);

    $encoded_table = json_encode($translation_table,
                                 JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (json_last_error() != 0) {
        throw new Exception(
            "Failed storing translation file: $tr_table_filename");
    }
    $fileSystem->file_put_contents($tr_table_filename, $encoded_table);
  }

  // For a given file on the local system, returns the translation table for the
  // provided locale.
  // @implements ILocalizer.
  public function getTranslationTable($filename, $locale) {
    if (!$this->fileSystem->file_exists($filename))  {
      throw new Exception("Template file does not exist: $filename");
    }
    $translation_table = array();
    $tr_table_filename = self::getTranslationTableFilename($filename,
                                                           $locale);
    if ($this->fileSystem->file_exists($tr_table_filename)) {
      $raw_data = $this->fileSystem->file_get_contents($tr_table_filename);

      $translation_table = json_decode($raw_data, true);

      if (json_last_error() != 0) {
        throw new Exception(
            "Failed decoding translation file: $tr_table_filename");
      }
    }

    return $translation_table;
  }

  // Sets the appropriate locale for the given HTTP request.
  public function setContextLocale($locale_variable = 'locale') {
    // TODO(kburnik): If no locale in session, we could use the GEO IP or
    // headers provided by the browser to select the appropriate locale.
    session_start();

    $candidates = array();
    if (array_key_exists($locale_variable, $_REQUEST)) {
      $candidates[] = $_REQUEST[$locale_variable];
    } else if (array_key_exists($locale_variable, $_SESSION)) {
      $candidates[] = $_SESSION[$locale_variable];
    } else {
      $candidates[] = $this->chooseLocaleFromHttp();
    }

    $this->selectCurrentLocale($candidates);
    $_SESSION[$locale_variable] = $this->getCurrentLocale();
  }

  // Tries to determine the best match for a locale based on the user agent
  // HTTP_ACCEPT_LANGUAGE header.
  private function chooseLocaleFromHttp() {
    $locales = array();
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      preg_match_all(
        '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
        $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        $lang_parse);

      if (count($lang_parse[1])) {
        // create a list like "en-us" => 0.8, "hr" => 0.6, ...
        $locale_names = array_map('strtolower', $lang_parse[1]);
        $locales = array_combine($locale_names, $lang_parse[4]);

        // set default to 1 for any without q factor
        foreach ($locales as $locale => $val) {
          if ($val === '')
            $locales[$locale] = 1;
        }

        // sort list based on value
        arsort($locales, SORT_NUMERIC);
      }
    }

    if (count($locales) > 0) {
      $supported_locales = $this->getSupportedLocales();

      // Go in order of preference announced by the user agent.
      foreach ($locales as $locale => $weight) {
        $language = self::localeToLanguage($locale);

        // Go in order of preference from supported locales.
        foreach ($supported_locales as $supported_locale) {
          $supported_language = self::localeToLanguage($supported_locale);

          // Try exact match.
          if ($locale == $supported_locale) {
            return $supported_locale;
          }

          // Try by language.
          if ($language == $supported_language) {
            return $supported_locale;
          }
        }
      }
    }

    return $this->getDefaultLocale();
  }

  private static function localeToLanguage($locale) {
    return reset(explode("-", $locale));
  }

  // Routes to the localized version of the static resource or if the resource
  // does not exist, loads the fallback script (which should handle a 404).
  // The resource_path should start with forward slash (e.g. /views/foo.html).
  public static function handleStaticResourceRequest($directory,
                                                     $resource_path,
                                                     $fallback_script) {
    $filename = $directory . $resource_path;
    if (!file_exists($filename)) {
      include_once($fallback_script);
    } else {
      echo get_once($filename);
    }
  }

  // Returns the filename which contains the json mapping of tokens to text in
  // the provided locale. The file does not have to exist.
  private static function getTranslationTableFilename($filename, $locale) {
    return "{$filename}.tr.{$locale}.json";
  }

  // Returns the filename which contains the applied translation.
  // The file does not have to exist.
  private static function getTranslationFilename($filename,
                                                 $locale,
                                                 $fileSystem) {
    if (!$fileSystem->file_exists($filename))  {
      throw new Exception("Template file does not exist: $filename");
    }
    $translation_dir = Project::GetProjectDir("/gen/translation/$locale");
    if (!$fileSystem->file_exists($translation_dir)) {
      $fileSystem->mkdir($translation_dir, 0755, true);
    }

    $project_path = $fileSystem->realpath(Project::GetProjectDir());
    $template_path = $fileSystem->realpath($filename);
    $relative_path = substr($template_path, strlen($project_path));

    return $translation_dir . '/' . friendly_url($relative_path);
  }
}
