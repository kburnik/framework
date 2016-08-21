#!/usr/bin/env php
<?php

include_once( dirname(__FILE__) . '/../base/Base.php' );

function translate_cli($command, $args, $cli, $fs, $pwd) {
  if (count(array_intersect($args, array("-h", "--help", "-?")))) {
    $usage = "Usage: $command " .
             "<action=assign|generate <template_filename>>";

    $cli->writeLine($usage);

    return 0;
  }

  $action = $args[0];
  $filename = $args[1];

  if (empty($filename) || !$fs->file_exists($filename)) {
    $cli->errorLine("File does not exist: $filename");
    return 1;
  }

  $template = $fs->file_get_contents($filename);


  $translation_table_filename =
      Translator::getTranslationTableFilename($filename);
  $table = array();
  if ($fs->file_exists($translation_table_filename)) {
    $table = json_decode(
        $fs->file_get_contents($translation_table_filename), true);
  }
  $default_lang = constant('PROJECT_LANGUAGE');
  $translator = new Translator($template, $table, $default_lang);
  $languages = explode(',', constant('PROJECT_LANGUAGE_LIST'));

  if (empty($table)) {
    $table = $translator->createTranslationTable($languages);
    $fs->file_put_contents($translation_table_filename,
                           json_encode(
                               $table,
                               JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $cli->writeLine("Created translation file: $translation_table_filename");
  }

  if ($action == "assign") {
    $parsed = $translator->parse(/*$annonymous=*/true);
    $used = array();
    foreach ($parsed as $index => $token) {
      $used[$token['token']] = true;
    }

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

    return 0;
  } else if ($action == "translate") {
    foreach ($languages as $lang) {
      $lang_template = $translator->translate($lang);
      $lang_filename = Translator::getLanguageFilename($filename, $lang);
      $fs->file_put_contents($lang_filename, $lang_template);
      $cli->writeLine("Saved language file: $lang_filename");
    }
  }

  return 0;
}

if (realpath($argv[0]) === __FILE__) {
  Project::Resolve(getcwd(), /*$config_only=*/true);
  ob_end_flush();
  ShellClient::create($argv, new FileSystem(), getcwd())
      ->start('translate_cli');
}
