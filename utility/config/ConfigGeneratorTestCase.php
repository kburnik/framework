<?php

include_once( dirname(__FILE__) . "/../../base/Base.php" );

class InMemoryFileSystem extends FileSystem {
  private $files;

  public function __construct($files = array()) {
    $this->files = $files;
  }

  // @override
  public function file_exists($filename) {
    return array_key_exists($filename, $this->files);
  }

  public function file_get_contents($filename,
                                    $flags=0,
                                    $context=null,
                                    $offset = 0,
                                    $maxlen = 10000000) {
    if (!$this->file_exists($filename))
      throw new Exception("Cannot open file.");

    return $this->files[$filename];
  }
}

class ConfigGeneratorTestCase extends TestCase {

  private $json;
  private $configGenerator;

  public function __construct() {

    $fs = new InMemoryFileSystem(array(
      "/path/to/project/project.json" => '{
        "PROJECT": {
          "DIR": "[__DIR__]",
          "NAME": "myapp",
          "AUTHOR": {
            "NAME": "John Doe"
          },
          "DATA_DIR": "[__DIR__]/data"
        },
        "SOME_PATH": "[PROJECT_DIR]/some/path",
        "@merge": [
          "[PROJECT_DIR]/one.json",
          "[PROJECT_DIR]/two.json"
        ]
      }',

      '/path/to/project/one.json' => '
          {
            "PROJECT":{
              "AUTHOR_EMAIL": "john@example.com"
            }
          }',
      '/path/to/project/two.json' => '{}',
    ));

    $this->configGenerator = new ConfigGenerator($fs, ".");
    $this->configGenerator->load("/path/to/project/project.json");
  }

  public function flatten_exampleConfig_justFlattens() {
    $flat_config = $this->configGenerator->flatten();

    $expected_flat_config = array (
      'PROJECT_DIR' => '[__DIR__]',
      'PROJECT_NAME' => 'myapp',
      'PROJECT_AUTHOR_NAME' => 'John Doe',
      'PROJECT_DATA_DIR' => '[__DIR__]/data',
      'SOME_PATH' => '[PROJECT_DIR]/some/path',
      '@merge' =>
        array (
          '[PROJECT_DIR]/one.json',
          '[PROJECT_DIR]/two.json',
      )
    );

    $this->assertEqual($expected_flat_config, $flat_config);
  }

  public function generate_exampleConfig_generates() {
    $config = $this->configGenerator->generate();
    $expected_config = array(
      'SOME_PATH' => '/path/to/project/some/path',
      'PROJECT_AUTHOR_EMAIL' => 'john@example.com',
      'PROJECT_AUTHOR_NAME' => 'John Doe',
      'PROJECT_DATA_DIR' => '/path/to/project/data',
      'PROJECT_DIR' => '/path/to/project',
      'PROJECT_NAME' => 'myapp',
    );

    $this->assertEqual($expected_config, $config);
  }

  public function compile_exampleConfig_compilesToPhp() {
    $php_code = $this->configGenerator->compile();
    $expected_php_code='<?php
// DO NOT EDIT! This is a generated config. Edit project-config.json instead.
--omitted--

// Generated config.
define(\'PROJECT_DIR\', \'/path/to/project\');
define(\'PROJECT_NAME\', \'myapp\');
define(\'PROJECT_AUTHOR_NAME\', \'John Doe\');
define(\'PROJECT_DATA_DIR\', \'/path/to/project/data\');
define(\'SOME_PATH\', \'/path/to/project/some/path\');
define(\'PROJECT_AUTHOR_EMAIL\', \'john@example.com\');

// Request specific values.
define(\'REQUEST_URI\', $_SERVER[\'REQUEST_URI\']);
define(\'HTTP_HOST\', $_SERVER[\'HTTP_HOST\']);
define(\'SERVER_PROTOCOL\', $_SERVER[\'SERVER_PROTOCOL\']);
define(\'REQUEST_METHOD\', $_SERVER[\'REQUEST_METHOD\']);
define(\'URL_QUERY\', ($_SERVER[\'QUERY_STRING\']) ?
                    \'?\' . $_SERVER[\'QUERY_STRING\'] : \'\');
';
    $php_code = preg_replace('/^\/\/ Generated on: .*$/m',
                             '--omitted--',
                             $php_code);
    $expected_php_code = str_replace("\r\n", "\n", $expected_php_code);
    $php_code = str_replace("\r\n", "\n", $php_code);

    $this->assertEqual($expected_php_code, $php_code);
  }

}
