<?php

class TestTask implements ITask {
  public function execute($arguments) {
    file_put_contents(dirname(__FILE__) . "/temp/$arguments",  "Done");
  }
}

