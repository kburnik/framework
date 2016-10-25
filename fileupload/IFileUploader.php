<?php

interface IFileUploader {
  // get list of supported mime types
  public function getAllowedFileTypes();
}

