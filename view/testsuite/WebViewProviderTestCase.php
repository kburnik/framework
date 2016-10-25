<?php

if (!defined('SKIP_DB'))
  define('SKIP_DB', true);

include_once(dirname(__FILE__) . "/../../.testproject/project.php");

class WebViewProviderTestCase extends TestCase {

  public function getTemplate_WithExistingResources() {
    $fs = new DummyFileSystem();

    $fs->file_put_contents("sample.view.html", "[@!css]\ncode\n[@!javascript]");

    $fs->file_put_contents("sample.view.json", '
      {
        "required":{
          "group":{
            "css":["style.css", "other.css"],
            "js":["main.js","other.js"]
          }
        }
      }');

    $viewProvider = new WebViewProvider(
        array("template"=>"sample.view.html"),
        $fs);

    $tpl = $viewProvider->getTemplate("template");
    $expected =
      "<!-- group -->\n" .
      "    <link href='style.css' rel='stylesheet' type='text/css'>\n" .
      "    <link href='other.css' rel='stylesheet' type='text/css'>\n" .
      "code\n" .
      "<!-- group -->\n" .
      "    <script src='main.js'></script>\n" .
      "    <script src='other.js'></script>";

    $this->assertEqual($expected, $tpl);
  }

  public function getTemplate_withNonExistingResources() {
    $fs = new DummyFileSystem();
    $fs->file_put_contents("sample.view.html", "[@!css]code\n[@!javascript]");
    $viewProvider = new WebViewProvider(
        array("template"=>"sample.view.html"),
        $fs);

    $tpl = $viewProvider->getTemplate("template");
    $this->assertEqual("<!-- NO CSS -->code\n<!-- NO JAVASCRIPT -->", $tpl);
  }

}

