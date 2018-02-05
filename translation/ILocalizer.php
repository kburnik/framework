<?php

// Represents the common interface for a localization helper.
interface ILocalizer {

  // Reads the localized version of file in project, if no localized version is
  // present the provided file contents should be returned.
  public function readFile($filename);

  // Attempts to determine a locale from the list of candidates in order.
  // If no candidates are found, uses the default locale from the localizer.
  public function selectCurrentLocale($candidates=array());

  // Get the currently used locale from the localizer.
  public function getCurrentLocale();

  // Get the default locale from the localizer. The default locale is used
  // in the templates as a seed reference of the actual language text.
  public function getDefaultLocale();

  // Returns the list of supported locales.
  public function getSupportedLocales();

  // For a given file on the local system, returns the translation table for the
  // provided locale.
  public function getTranslationTable($filename, $locale);
}

