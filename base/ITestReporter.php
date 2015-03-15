<?php

interface ITestReporter {
  public function reportEvent($eventName, $eventArgs);
}
