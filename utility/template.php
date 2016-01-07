<?php

include_once(dirname(__FILE__) . "/common_templates.php");

function produce($template,
                 $data = array(),
                 $use_cache = true,
                 $do_warn = true,
                 $do_validate = true) {
  $tpl = new Tpl(false);

  return $tpl->produce($template, $data, $use_cache, $do_warn, $do_validate);
}

function produceview($filename,
                     $data,
                     $use_cache = true,
                     $do_warn = true,
                     $do_validate = true) {
  return produce(get_once($filename),
                 $data,
                 $use_cache,
                 $do_warn,
                 $do_validate);
}
