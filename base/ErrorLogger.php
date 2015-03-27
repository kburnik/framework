<?php

class ErrorLogger implements IErrorLogger {
  public function log($message) {
    error_log($message);
  }
}
