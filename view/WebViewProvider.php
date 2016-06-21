<?php

class WebViewProvider extends FileViewProvider {

  // Current section context (e.g. public/admin).
  private $section = null;

  public function __construct(
      $map = array(),
      IFileSystem $filesystem = null,
      $section = null) {
    parent::__construct($map, $filesystem);
    $this->section = $section;
  }

  // @throws Exception
  private function parseResourceFile($resource_filename) {
    $raw_resources = $this->filesystem->file_get_contents($resource_filename);
    $resources = json_decode($raw_resources, true);

    // Large expection handler for malformed json.
    if (!is_array($resources)) {
      $constants = get_defined_constants(true);
      $json_errors = array();
      foreach ($constants["json"] as $name => $value)
        if (!strncmp($name, "JSON_ERROR_", 11))
           $json_errors[$value] = $name;

      throw new Exception(
          "Malformed template resource file: $resource_filename\n"
          . $json_errors[json_last_error()]
          );
    }

    assert(is_array($resources["required"]),
           "Expected 'required' field in template " .
           "resources in $resource_filename.");

    return $resources;
  }

  function getTemplate($viewKey) {
    $resources = $this->getResources($viewKey, $this->section);
    $template = parent::getTemplate($viewKey);

    return strtr($template, $resources);
  }

  function getResources($viewKey, $section) {
    $template_filename = $this->map[$viewKey];
    $resource_filename = preg_replace("/.html$/", ".json", $template_filename);

    $javascript = "";
    $css = "";

    if ($this->filesystem->file_exists($resource_filename)) {
      $resources = $this->parseResourceFile($resource_filename);

      $sections = array('required', $this->section);

      foreach ($sections as $section){
        if (!array_key_exists($section, $resources))
          continue;

        foreach ($resources[$section] as $group => $res) {
          $javascript .= "    <!-- $section:$group -->\n";
          if (isset($res['js']))
            foreach ($res['js'] as $js_resource)
              $javascript .= "    " . javascript($js_resource) . "\n";

          $css .= "    <!-- $section:$group -->\n";
          if (isset($res['css']))
            foreach ($res['css'] as $css_resource)
              $css .= "    " . css($css_resource) . "\n";

          if (isset($res['less'])) {
            foreach ($res['less'] as $less_resource) {
              if (!defined('PRODUCTION_MODE')) {
                $css .= "    " . "<link rel='stylesheet/less' " .
                        "type='text/css' href='{$less_resource}'>\n";
              } else {
                $css .= "    " .
                    css(preg_replace("/.less$/", ".min.css", $less_resource)) .
                    "\n";
              }
            }
          }
        }
      }
    } else {
      $css ="<!-- NO CSS -->\n";
      $javascript ="<!-- NO JAVASCRIPT -->\n";
    }

    return array("[@!css]" => trim($css),
                 "[@!javascript]" => trim($javascript));
  }
}
