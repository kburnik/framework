<?php
include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TranslatorTestCase extends TestCase {

  public function parse_singleToken_parsed() {
    $template = "<p><tr:tr1>Hello\n\tworld</tr:tr1></p>";

    $this->assertTokens(
      array(
        array(
          'match' => "<tr:tr1>Hello\n\tworld</tr:tr1>",
          'value' => "Hello\n\tworld",
          'token' => 'tr1'
        )
      ),
      $template);
  }

  public function parse_duplicateTokenDifferentValue_parsed() {
    $template = '<p><tr:tr1>Hello</tr:tr1><tr:tr1>World</tr:tr1></p>';

    $this->assertException(
      "Duplicate token 'tr1' with different value detected: " .
      "<tr:tr1>World</tr:tr1>",
      $template);
  }

  public function translate_simpleTemplateWithTable_generatesInTargetLanguage() {
    $lang_table = array(
      "tr1" => array(
        "hr" => "Zdravo",
        "en" => "Hello"
        ),
      "tr2" => array(
        "hr" => "svijete",
        "en" => "world"
        )
    );
    $template = "<p><tr:tr1>Ola</tr:tr1>,\n<tr:tr2>mundo</tr:tr2>!</p>";

    $this->assertTranslated(
        "<p>Zdravo,\nsvijete!</p>",
        $template,
        $lang_table,
        "hr");
    $this->assertTranslated(
        "<p>Hello,\nworld!</p>",
        $template,
        $lang_table,
        "en");
  }

  public function createTranslationTable_sampleTemplate_createsTable() {
    $template = "<p><tr:tr1>Zdravo</tr:tr1>, <tr:tr2>svijete</tr:tr2>!</p>";
    $expected = array(
      "tr1" => array(
        "hr" => "Zdravo",
        "en" => "",
        ),
      "tr2" => array(
        "hr" => "svijete",
        "en" => "",
      ),
    );

    $this->assertTranslationTable($expected,
                                  $template,
                                  /*$default_lang=*/"hr",
                                  /*$languages=*/array("hr", "en"));

    // Check if the default is implicit in the language list.
    $this->assertTranslationTable($expected,
                                  $template,
                                  /*$default_lang=*/"hr",
                                  /*$languages=*/array("en"));
  }

  public function parseAnnonymous_twoTokens_parses() {
    $template = "<p><tr:>First</tr:> <tr:>Second</tr:></p>";
    $this->assertTokens(
        array(
          array(
            "match" => "<tr:>First</tr:>",
            "token" => "",
            "value" => "First",
          ),
          array(
            "match" => "<tr:>Second</tr:>",
            "token" => "",
            "value" => "Second",
          ),
        ),
        $template,
        /*$annonymous=*/true);
  }

  public function assign_twoTokens_assigned() {
    $template = "<p><tr:>First</tr:> <tr:>Second</tr:></p>";
    $this->assertAssigned(
        "<p><tr:one>First</tr:one> <tr:two>Second</tr:two></p>",
        array(
          array(
            "match" => "<tr:>First</tr:>",
            "token" => "one",
            "value" => "First",
          ),
          array(
            "match" => "<tr:>Second</tr:>",
            "token" => "two",
            "value" => "Second",
          ),
        ),
        $template);
  }

  private function assertTokens($expected, $template, $annonymous = false) {
    $translator = new Translator($template);
    $tokens = $translator->parse($annonymous);
    $this->assertEqual($expected, $tokens);
  }

  private function assertAssigned($expected, $parsed, $template) {
    $translator = new Translator($template);
    $new_template = $translator->assign($parsed);
    $this->assertEqual($expected, $new_template);
  }

  private function assertTranslated($expected, $template, $lang_table, $lang) {
    $translator = new Translator($template, $lang_table);
    $translation = $translator->translate($lang);
    $this->assertEqual($expected, $translation);
  }

  private function assertTranslationTable($expected,
                                          $template,
                                          $default_lang,
                                          $languages) {
    $translator = new Translator($template, array(), $default_lang);
    $table = $translator->createTranslationTable($languages);
    $this->assertEqual($expected, $table);
  }

  private function assertException($expected, $template) {
    $translator = new Translator($template);
    try {
      $tokens = $translator->parse();
      $this->assertNotReached("Should have thrown");
    } catch (Exception $ex) {
      $this->assertEqual($expected, $ex->getMessage());
    }
  }

}
