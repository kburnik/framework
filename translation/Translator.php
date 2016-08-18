<?php

class Translator {
  private $template;
  private $lang_table;
  private $default_lang;

  public function __construct($template,
                              $lang_table = array(),
                              $default_lang = "en") {
    $this->template = $template;
    $this->lang_table = $lang_table;
    $this->default_lang = $default_lang;
  }

  public function parse() {
    $matches = array();
    preg_match_all(
        '/\<\!tr:(?<token>([A-Za-z0-9-\+_]+))\>(?<value>(.*?))\<\\/\!tr:(?&token)\>/u',
        $this->template,
        $matches);
    $results = array();
    assert(count($matches['token']) == count($matches['value']));
    $c = count($matches['token']);
    $token_map = array();
    for ($i=0; $i < $c; $i++) {
      $token = $matches["token"][$i];
      $value = $matches["value"][$i];
      $results[] = array(
        "match" => $matches[0][$i],
        "token" => $token,
        "value" => $value);
      self::check(
        !array_key_exists($token, $token_map) || $token_map[$token] == $value,
        "Duplicate token 'tr1' with different value detected: " .
            $matches[0][$i]);

      $token_map[$token] = $value;
    }
    return $results;
  }

  // Creates the initial translation table from a template. Placeholders are
  // created for the listed languages, and the default language is filled in.
  public function createTranslationTable($languages) {
    $tokens = $this->parse();
    $table = array();
    $languages = array_merge(array($this->default_lang), $languages);
    foreach ($tokens as $token) {
      foreach ($languages as $lang) {
        $table[$token["token"]][$lang] =
            ($lang == $this->default_lang) ? $token["value"] : "";
      }
    }
    return $table;
  }

  public function translate($lang) {
    $tokens = $this->parse();
    $replacement = array();
    foreach ($this->lang_table as $token => $translation) {
      $this->check(
          array_key_exists($lang, $translation),
          "Missing language in translation table: $lang at token: $token");
      $begin = "\<\!tr:$token\>";
      $end = "\<\/\!tr:$token\>";
      $pattern[] = "/{$begin}(.*?){$end}/u";
      $replacement[] = $translation[$lang];
    }
    return preg_replace($pattern, $replacement, $this->template);
  }

  private static function check($value, $message) {
    if (!$value) {
      throw new Exception($message);
    }
  }

}
