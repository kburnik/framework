<?php

// Represents the common interface for a localization helper.
interface ILocalizer {

  // Reads the localized version of file in project, if no localized version is
  // present the provided file contents should be returned.
  public function readFile($filename);
}
