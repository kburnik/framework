<?php

if (!defined('TPL_STD_DATE')) {
  $tpl = array(
    "STD_DATE" => "Y-m-d",
    "STD_TIME" => "H:i:s",
    "STD_DATETIME" => "Y-m-d H:i:s",
    "STD_CSV" => "\$[,]{[*]}",
    "STD_UL" => "<ul>\${<li>[*]</li>}</ul>",
    "STD_LINK_LIST" => "<ul>\${<li><a href='[url:urlencode]'>[title]</a></li>}</ul>",
    "STD_OL" => "<ol>\${<li>[*]</li>}</ol>",
    "STD_MAP" => "
      <table border='1'>
        <thead>
          <tr>
            <th>KEY</th>
            <th>VALUE</th>
          </tr>
        </thead>
        <tbody>
        \${
          <tr>
            <th>[#]</th>
            <th>[*]</th>
          </tr>
        }
      </table>",
    "STD_TABLE" => "
      <table border='1'>
        <thead>
          <tr>
            $([*.0]) {<th>[#|&nbsp;]</th>}
          </tr>
        </thead>
        <tbody>
        \${
          <tr>
            \${ <td>[*|&nbsp;]</td> }
          </tr>
        }
        </tbody>
      </table>",
    "STD_TABLE_ROWS" => "
        \${
          <tr>
            \${ <td>[*|&nbsp;]</td> }
          </tr>
        }
        ",
    "DEBUG"=>"
      \${
      <div style='border:1px solid #aaaaaa; padding:3px; margin:2px;font:16px courier new;'>
        <span style='font-weight:bold; color:red;width:24%;display:inline-block;'>[stack_top]</span>
        <span style='font-weight:bold; color:#114488;width:24%;display:inline-block;'>[identifier]</span>
        <span style='color:black;width:24%;display:inline-block;'>[arguments]</span>
        <span style='color:green;width:24display:inline-block;'>[result]</span>
      </div>
      }
    ",
    "JS" => "<script src=\"[*]\" type=\"text/javascript\"></script>" ,
    "CSS" => "<link href=\"[*]\" type=\"text/css\" rel=\"stylesheet\" />"
  );

  foreach ($tpl as $var=>$val) {
    define("TPL_{$var}",$val);
  }
}
