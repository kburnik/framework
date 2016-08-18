<?php
include_once( dirname(__FILE__) . "/../../base/Base.php" );

class TranslatorTestCase extends TestCase {

  public function parse_singleToken_parsed() {
    $template = '<p><!tr:tr1>Hello world</!tr:tr1></p>';
    $this->assertTokens(
      array(
        array(
          'match' => '<!tr:tr1>Hello world</!tr:tr1>',
          'value' => 'Hello world',
          'token' => 'tr1'
        )
      ),
      $template);
  }

  public function parse_duplicateTokenDifferentValue_parsed() {
    $template = '<p><!tr:tr1>Hello</!tr:tr1><!tr:tr1>World</!tr:tr1></p>';
    $this->assertException(
      "Duplicate token 'tr1' with different value detected: " .
      "<!tr:tr1>World</!tr:tr1>",
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
    $template = "<p><!tr:tr1>Ola</!tr:tr1>, <!tr:tr2>mundo</!tr:tr2>!</p>";
    $this->assertTranslated(
        "<p>Zdravo, svijete!</p>",
        $template,
        $lang_table,
        "hr");
    $this->assertTranslated(
        "<p>Hello, world!</p>",
        $template,
        $lang_table,
        "en");
  }

  private function assertTokens($expected, $template) {
    $translator = new Translator($template);
    $tokens = $translator->parse();
    $this->assertEqual($expected, $tokens);
  }

  private function assertTranslated($expected, $template, $lang_table, $lang) {
    $translator = new Translator($template, $lang_table);
    $translation = $translator->translate($lang);
    $this->assertEqual($expected, $translation);
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
