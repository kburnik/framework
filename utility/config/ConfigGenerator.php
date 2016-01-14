<?php

class ConfigGenerator {

  private $file_system;
  private $directory; // Directory of the last loaded json.
  private $config;

  private $config_files; // List of config files which we can check for changes.

  public function __construct($file_system = null, $default_directory = ".") {
    $this->file_system =
        ($file_system === null) ? new FileSystem() : $file_system;

    $this->config = array();
    $this->directory = $default_directory;
    $this->config_files = array();
  }

  public function load($filename) {
    $this->directory = $this->file_system->dirname($filename);
    $json = $this->file_system->file_get_contents($filename);
    $this->config_files[] = array($filename, md5($json));
    $this->decodeJson($json);
  }

  public function flatten() {
    return $this->flattenImpl($this->config);
  }

  public function generate() {
    $flat_config = $this->flatten();
    $data = array();
    $actions = array();

    // Resolve config values.
    foreach ($flat_config as $key => $template) {

      $data = array_merge($flat_config,
                    array("__DIR__" => $this->directory));

      // Resolve the value of this config. It can be an array of items
      // (e.g. arguments for an action) or a single item.
      $value = $this->resolveValue($template, $data);

      // Schedule actions.
      if ($key[0] == '@') {
        $action_name = "action_" . substr($key,1);

        if (!method_exists($this, $action_name))
          throw new Exception("Invalid action: $key");

        $actions[$action_name] = $value;

        // Remove action from the flat config to avoid recursion.
        unset($flat_config[$key]);

        continue;
      }

      $flat_config[$key] = $value;
    }

    // Apply actions.
    foreach ($actions as $action_name => $value)
      $flat_config = $this->$action_name($value, $flat_config);

    return $flat_config;
  }

  public function compile() {
    $template = file_get_contents(dirname(__FILE__) .
                                  "/project-config.php.tpl");

    $flat_config = $this->generate();
    $data = array("config" => $this->normalize($flat_config));

    return $this->produce($template, $data);
  }

  public function save($filename) {
    $code = $this->compile();

    return $this->file_system->file_put_contents($filename, $code);
  }

  private function normalize($flat_config) {
    foreach ($flat_config as $k => $v)
      $flat_config[$k] = var_export($v, true);

    return $flat_config;
  }

  private function flattenImpl($node, $path=array()) {
    if (!is_array($node))
      return array(implode("_", $path) => $node);

    $flat_config = array();
    foreach ($node as $name => $subnode) {
      if ($name[0] == '@') {
        $flat_config[$name] = $subnode;
        continue;
      }

      $subpath = array_merge($path, array($name));
      $flat_config = array_merge($flat_config,
                                 $this->flattenImpl($subnode, $subpath));
    }

    return $flat_config;
  }

  private function resolveValue($mixed, $context) {
    if (!is_array($mixed))
      return $this->produce($mixed, $context);

    foreach ($mixed as $k => $v)
      $mixed[$k] = $this->produce($v, $context);

    return $mixed;
  }

  private function decodeJson($json) {
    $decoded_json = json_decode($json, true);

    if (json_last_error())
      throw new Exception(json_last_error_msg());

    $this->config = $decoded_json;
  }

  private function action_merge($merge_order, $flat_config) {
    foreach ($merge_order as $filename) {
      if (!$this->file_system->file_exists($filename))
        continue;

      $this->load($filename);
      $flat_config = array_merge($flat_config, $this->generate());
    }

    return $flat_config;
  }

  private function produce($template, $data) {
    return produce($template, $data, false, false, false);
  }

}
