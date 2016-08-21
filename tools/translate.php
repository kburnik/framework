#!/usr/bin/env php
<?php

include_once( dirname(__FILE__) . '/../base/Base.php' );

function translate_cli($command, $args, $cli, $fs, $pwd) {
  if (count(array_intersect($args, array("-h", "--help", "-?")))) {
    $usage = "Usage: $command " .
             "<action=assign <template_filename>>";

    $cli->writeLine($usage);

    return 0;
  }

  if ($args[0] == "assign") {
    $filename = $args[1];

    if (empty($filename) || !$fs->file_exists($filename)) {
      $cli->errorLine("File does not exist: $filename");
      return 1;
    }

    $template = $fs->file_get_contents($filename);
    $translator = new Translator($template);

    $parsed = $translator->parse(/*$annonymous=*/true);
    $used = array();
    foreach ($parsed as $index => $token) {
      if (!empty($token['token']))
        continue;

      do {
        $new_token = $cli->input("{$token['match']}: ");
      } while(!empty($new_token) && $used[$new_token]);
      $used[$new_token] = true;

      $parsed[$index]['token'] = $new_token;
    }

    $new_template = $translator->assign($parsed);
    $fs->file_put_contents($filename, $new_template);
    $cli->writeLine("Saved template: $filename");
  }

  return 0;
}


if (realpath($argv[0]) === __FILE__) {
  ob_end_flush();
  ShellClient::create($argv, new FileSystem(), getcwd())
      ->start('translate_cli');
}
