<?php

// Represents the common interface for a localization helper.
interface ILocalizer {

  // Reads the localized version of file in project, if no localized version is
  // present the provided file contents should be returned.
  public function readFile($filename);

  // Attempts to determine a locale from the list of candidates in order.
  // If no candidates are found, uses the default_locale.
  public function selectLocale($default_locale="en-us",
                               $candidates=array(),
                               $supported_locales=array());
}
