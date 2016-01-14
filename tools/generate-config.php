#!/usr/bin/env php
<?php

include_once(dirname(__FILE__) . "../base/Base.php");


function generate_config_cli($command, $args, $cli, $fs,
                             $pwd,
                             $project_config_basename,
                             $project_config_generated_basename) {
  $project_config_source_filename = "$pwd/$project_config_basename";

  if (count(array_intersect($args, array("-h", "--help", "-?")))) {
    $usage = "Usage: $command " .
             "[src=$project_config_source_filename]";

    $cli->writeLine($usage);

    return 0;
  }

  if (count($args))
    $project_config_source_filename = $args[1];

  if (!$fs->file_exists($project_config_source_filename))
    $cli->errorLine("Cannot find $project_config_source_filename.");

  $project_directory = $fs->dirname($project_config_source_filename);
  $project_config_generated_filename = $project_directory . '/' .
      $project_config_generated_basename;
  $generator = new ConfigGenerator($fs, $project_directory);
  $ok = $generator->save($project_config_generated_filename);

  if (!$ok){
    $this->errorLine("Failed saving config to: $project_config_filename");
    return 1;
  }

  $this->writeLine(
      "Written project config to: $project_config_generated_filename");

  return 0;
}

if (realpath($argv[0]) === __FILE__) {
  ShellClient::create($argv, new FileSystem(), getcwd(), 'project-config.json',
    'project-config.php')
      ->start('generate_config_cli');
}
