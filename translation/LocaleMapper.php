<?php

// Handles translation of a single table with the provided translation table.
class LocaleMapper {
  private $template;
  private $translation_table;

  public function __construct($template,
                              $translation_table = array()) {
    $this->template = $template;
    $this->translation_table = $translation_table;
  }

  // Returns the translation table.
  public function getTranslationTable() {
    return $this->translation_table;
  }

  // Updates the internal language table from the template.
  // The values from the initial table are preserved, they're only set if the
  // key is new. Returns whether any updates have been applied.
  public function update() {
    $parsed_tokens = $this->parse();
    $updated = false;
    foreach ($parsed_tokens as $p) {
      if (!array_key_exists($p['token'], $this->translation_table)) {
        $this->translation_table[$p['token']] = $p['value'];
        $updated = true;
      }
    }

    return $updated;
  }

  // Maps the translation table to the template and returns the rendered
  // localized template.
  public function apply() {
    $tokens = $this->parse();
    $replacement = array();
    foreach ($this->translation_table as $token => $translation) {
      $begin = "\<tr:$token\>";
      $end = "\<\/tr:$token\>";
      $pattern[] = "/{$begin}(.*?){$end}/us";
      $replacement[] = $translation;
    }

    // Remove all other tokens which have no translation.
    $begin = "\<tr:[A-Za-z0-9-_]*\>";
    $end = "\<\/tr:[A-Za-z0-9-_]*\>";
    $pattern[] = "/{$begin}(.*?){$end}/us";
    $replacement[] = "\$1";

    return preg_replace($pattern, $replacement, $this->template);
  }

  private static function check($value, $message) {
    if (!$value) {
      throw new Exception($message);
    }
  }

  // Parses a template for translations tokens. Returns array of matched tokens.
  private function parse() {
    $matches = array();
    $token_pattern = '[A-Za-z0-9-\+_]+';
    preg_match_all(
        '/\<tr:(?<token>(' . $token_pattern .
        '))\>(?<value>(.*?))\<\\/tr:(?&token)\>/us',
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
        "Duplicate token '$token' with different value detected: " .
            $matches[0][$i]);

      $token_map[$token] = $value;
    }

    return $results;
  }
}
