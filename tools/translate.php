#!/usr/bin/env php
<?php

include_once( dirname(__FILE__) . '/../base/Base.php' );

function translate_cli($command, $args, $cli, $fs, $pwd) {
  if (count(array_intersect($args, array("-h", "--help", "-?")))) {
    $usage = "Usage: $command " .
             "<action=build <template_filename>>";

    $cli->writeLine($usage);

    return 0;
  }

  $action = $args[0];
  $filename = $args[1];

  if (empty($filename) || !$fs->file_exists($filename)) {
    $cli->errorLine("File does not exist: $filename");
    return 1;
  }

  if ($action == "build") {
    $cli->writeLine("Building locale table files.");
    $localizer = Project::GetCurrent()->getLocalizer();
    DefaultLocalizer::rebuildTranslationTables($filename, $localizer, $fs);
  } else {
    $cli->errorLine("Unknown action: $action");
    return 1;
  }

  return 0;
}

if (realpath($argv[0]) === __FILE__ || realpath($argv[1]) === __FILE__) {
  Project::Resolve(getcwd(), /*$config_only=*/false);
  ob_end_flush();
  ShellClient::create($argv, new FileSystem(), getcwd())
      ->start('translate_cli');
}
