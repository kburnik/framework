<?php

// Flag types
// script <required-values>
// script [optional-values]
// -b  boolean flag
// -o:: optional value of given type
// -r: required value of given type
// -l:: optional list of strings (array)

class ShellArg {
  // No flag is required. Parameters are listed.
  const OPTION_ARGUMENTS = "NO_FLAG";

  const OPTION_REQUIRED = true;
  const OPTION_OPTIONAL = false;

  const OPTION_FLAG = "OPTION_FLAG";
  const OPTION_ONE = "OPTION_ONE";
  const OPTION_ONE_OR_MORE = "OPTION_ONE_OR_MORE";
  const OPTION_ZERO_OR_MORE = "OPTION_ZERO_OR_MORE";

  const EXCEPTION_UNKNOWN_OPTION = 1;
  const EXCEPTION_MISSING_REQUIRED_VALUE = 2;
  const EXCEPTION_UNEXPECTED_CARDINALITY = 3;
  const EXCEPTION_MISSING_REQUIRED_OPTION = 4;
  const EXCEPTION_INVALID_CARDINALITY = 5;
  const EXCEPTION_INVALID_OPTION_SPECIFIER = 6;

  private $match;
  private $required;
  private $cardinality;
  private $description;
  private $defaultValue;

  public function __construct($match,
                              $required,
                              $cardinality,
                              $description,
                              $defaultValue = null) {

    if (!($match == ShellArg::OPTION_ARGUMENTS ||
        (is_array($match) && count($match) == 2 && $match[0] != $match[1]
         && is_string($match[0]) && is_string($match[1]) && !empty($match[0]) &&
         !empty($match[1])) || is_string($match)))
      throw new Exception("Invalid option specifier.",
                          ShellArg::EXCEPTION_INVALID_OPTION_SPECIFIER);

    if (!in_array($cardinality, array(self::OPTION_FLAG,
                                      self::OPTION_ONE,
                                      self::OPTION_ONE_OR_MORE,
                                      self::OPTION_ZERO_OR_MORE,
                                      )))
      throw new Exception("Invalid cardinality",
                          ShellArg::EXCEPTION_INVALID_CARDINALITY);

    if ($cardinality == ShellArg::OPTION_FLAG && $required)
      throw new Exception("Flag option cannot be required.",
                          ShellArg::EXCEPTION_INVALID_CARDINALITY);

    switch ($cardinality) {
      case ShellArg::OPTION_FLAG:
        $defaultValue = ($defaultValue === null) ? false : $defaultValue;

        if (!is_bool($defaultValue))
          throw new Exception("Invalid default value for option.",
                              ShellArg::EXCEPTION_INVALID_DEFAULT_VALUE);

        break;
      case ShellArg::OPTION_ONE:
        $defaultValue = ($defaultValue === null) ? "" : $wdefaultValue;

        if (!is_string($defaultValue))
          throw new Exception("Invalid default value for option.",
                              ShellArg::EXCEPTION_INVALID_DEFAULT_VALUE);

        break;
      case ShellArg::OPTION_ZERO_OR_MORE:
        $defaultValue = ($defaultValue === null) ? array() : $defaultValue;

        if (!is_array($defaultValue))
          throw new Exception("Invalid default value for option.",
                              ShellArg::EXCEPTION_INVALID_DEFAULT_VALUE);

        break;
    }

    $this->match = $match;
    $this->required = $required;
    $this->cardinality = $cardinality;
    $this->description = $description;
    $this->defaultValue = $defaultValue;
  }

  private function matchesName($optionName) {
    if ($optionName == null && $this->match == ShellArg::OPTION_ARGUMENTS)
      return true;

    if (is_string($this->match) && $this->match == $optionName)
      return true;

    return (is_array($this->match) && in_array($optionName, $this->match));
  }

  private static function findOption($options, $optionName) {
    foreach ($options as $option) {
      if ($option->matchesName($optionName))
        return $option;
    }

    return null;
  }

  private static function removeOption($options, $optionName) {
    foreach ($options as $i => $option) {
      if ($option->matchesName($optionName))
        unset($options[$i]);
    }

    return $options;
  }

  private function getDefaults() {
    $defaults = array();

    foreach ($this->getNames() as $key) {
      switch ($this->cardinality) {
        case ShellArg::OPTION_FLAG:
        case ShellArg::OPTION_ZERO_OR_MORE:
          $defaults[$key] = $this->defaultValue;
          break;
        default:
          // TODO(kburnik): This should be another exception.
          throw new Exception(
                "Unexpected cardinality for option: $key ($this->cardinality)",
                ShellArg::EXCEPTION_UNEXPECTED_CARDINALITY);
      }
    }

    return $defaults;
  }

  private function getNames() {
    if (is_string($this->match))
      return array($this->match);

    if ($this->match == ShellArg::OPTION_ARGUMENTS)
      return array("__arguments__");

    return $this->match;
  }

  public static function parse($argv, $options) {
    $arguments = array();

    $previos_chunk = null;
    $option = null;
    $optionName = null;
    $expect_required_value = false;
    $cardinality = ShellArg::OPTION_ZERO_OR_MORE;

    $arguments["__command__"] = array_shift($argv);
    $arguments["__arguments__"] = array();

    $satisfied = array();

    // Add sentinel.
    $argv[] = "--";

    foreach ($argv as $index => $chunk) {
      if ($option == null) {
        $expect_required_value = false;
        $cardinality = ShellArg::OPTION_ZERO_OR_MORE;
        $keys = array("__arguments__");
        $optionName = null;
      } else {
        $keys = $option->getNames();
      }

      if ($chunk[0] == '-') {

        if ($expect_required_value)
          throw new Exception("Missing required value for $previos_chunk.",
                              ShellArg::EXCEPTION_MISSING_REQUIRED_VALUE);

        if ($chunk[1] == '-') {
          $optionName = substr($chunk, 2);
        } else {
          $optionName = substr($chunk, 1);
        }

        // Sentinel.
        if ($optionName == ""){
          $option = null;

          continue;
        }

        $option = self::findOption($options, $optionName);

        if ($option == null) {
          throw new Exception("Unknown option: $chunk.",
                              ShellArg::EXCEPTION_UNKNOWN_OPTION);
        }

        // Handle the FLAG option right away.
        if ($option->cardinality == ShellArg::OPTION_FLAG) {
          foreach ($option->getNames() as $key) {
            $arguments[$key] = true;
            $satisfied[$key] = true;
          }
          $options = self::removeOption($options, $optionName);
          $option = null;

          continue;
        }

        $expect_required_value = ($option->cardinality == ShellArg::OPTION_ONE);
        $cardinality = $option->cardinality;
        $previos_chunk = $chunk;

        continue;
      }

      foreach ($keys as $key) {
        switch ($cardinality) {
          case ShellArg::OPTION_ONE:
            $satisfied[$key] = true;
            $arguments[$key] = $chunk;
            $options = self::removeOption($options, $optionName);
            $option = null;
            break;
          case ShellArg::OPTION_ONE_OR_MORE:
          case ShellArg::OPTION_ZERO_OR_MORE:
            $satisfied[$key] = true;
            $arguments[$key][] = $chunk;
            break;
          default:
            throw new Exception("Unexpected cardinality: $cardinality",
                                ShellArg::EXCEPTION_UNEXPECTED_CARDINALITY);
        }
      } // foreach keys

    } // foreach argv

    // Remove the ARGUMENTS list option.
    $options = self::removeOption($options, null);

    // Apply defaults to options.
    foreach ($options as $option) {
      $skip = false;
      // Skip satisfied options.
      foreach ($option->getNames() as $key) {
        if (array_key_exists($key, $satisfied)) {
          $skip = true;
          break;
        }

        $chunk = "-" . $key;
      }

      if ($skip)
        continue;

      if ($option->cardinality == ShellArg::OPTION_ONE_OR_MORE)
        throw new Exception("Missing required value for $chunk.",
                            ShellArg::EXCEPTION_MISSING_REQUIRED_VALUE);

      if ($option->required)
        throw new Exception("Missing required option: $chunk.",
                            ShellArg::EXCEPTION_MISSING_REQUIRED_OPTION);

      $arguments = array_merge($arguments, $option->getDefaults());
    }

    return $arguments;
  }

}
