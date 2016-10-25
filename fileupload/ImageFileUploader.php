<?php

abstract class ImageFileUploader extends FileUploader {

  function getAllowedFileTypes() {
    return array(
      "image/x-jg",
      "image/fif",
      "image/gif",
      "image/x-icon",
      "image/ief",
      "image/jpeg",
      "image/pjpeg",
      "image/jpeg",
      "image/x-jps",
      "image/png",
      "image/x-png"
    );
  }
}

