Templates
=========

Framework includes it's very own template language for producing views. This is
the preferred way to work with views. Instead of having PHP files with a bunch
of '<?php ?>' blocks, you can write simple templates and keep the code and view
logic separate.

We can assume most data is stored in tables so we can have a simple table such as this one:

+----+--------+----------+
| ID | NAME   | SURNAME  |
+====+========+==========+
| 1  | Jimmy  | Hendrix  |
+----+--------+----------+
| 2  | James  | Hetfield |
+----+--------+----------+
| 3  | Dexter | Holland  |
+----+--------+----------+


We can also assume that such a table is stored in a MySQL table, and a PHP script is fetching those rows to an array of a following structure:

.. code-block:: php

    <?php
    $data = array(
      array("ID" => "1", "name" => "Jimmy", "surname" => "Hendrix"),
      array("ID" => "2", "name" => "James", "surname" => "Hetfield"),
      array("ID" => "3", "name" => "Dexter", "surname" => "Holland"),
    );

In order to display this simple data structure as a table in an HTML document, we would need a generic function like this:

.. code-block:: php

    <?php
    function display_as_table($data) {
      if (!is_array($data) || count($data) == 0)
        return "No data";

      // extract the keys (field names)
      $keys = array_keys( reset($data) );

      // generate the table header
      $header="";
      foreach ( $keys as $field ) {
        $header .= "<th>{$field}</th>";
      }

      // generate the table rows
      $rows="";
      foreach ( $data as $row ) {
        $rows.="<tr>";
        foreach ( $row as $field => $cell )  {
          $rows.= "<td>{$cell}</td>";
        }
        $rows.="</tr>";
      }

      // construct the table with the header and rows
      return "
      <table border='1'>
        <thead><tr>{$header}</tr></thead>
        <tbody>{$rows}</tbody>
      </table>
      ";
    }

How ever useful this might seem, it's not a good way to go, because not all data
is simple as this, often you need to provide links, styles and other view
associated information... By expanding this function, you would be using a ton
of parameters or objects to describe how you want your view to be displayed. It's a very easy way to make your code hard to maintain.

A simpler and cleaner way would be to describe this view generating process in a
separate view file named **std.table.view.html**:

.. code-block:: html

    <table border='1'>
      <thead>
        <tr>
          $([*.0]) {<th>[#]</th>}
        </tr>
      </thead>
      <tbody>
      ${
        <tr>
          ${ <td>[*]</td> }
        </tr>
      }
      </tbody>
    </table>


And whenever you want to produce a standard table, just make a simple call:

.. code-block:: php

    <?php
    echo produceview('std.table.view.html', $data);

The template language
---------------------

Let's take a simpler example, we want to produce an ordered list:

  1. One
  2. Two
  3. Three

.. code-block:: html

    <ol>
      <li>One</li>
      <li>Two</li>
      <li>Three</li>
    </ol>

So, here's what we need to construct the view:

.. code-block:: php

    <?php
    // The view template.
    $template = '<ol> ${ <li>[*]</li> } </ol>';

    // The data being presented by the view.
    $data = array("One", "Two", "Three");

    // Output the view.
    echo produce( $template , $data );


Let's now explain the template language.

First of all, the view expects some data to work with, that data is usually an array or a string. The data you pass to the view via the **produce** or **produceview** function is called the root data. So our root data is array("One", "Two", "Three").

The other thing the view knows is it's **current value context**. The current value context is denoted by **[*]** and the view always starts with the root data as it's current value context.

The view also knows it's current key context which is denoted by [#]. Root context however does not have a key (only data), so there is no such thing as a root key context.

In order to produce the list, we need to iterate over the root data values.
We do so by writing:

.. code-block:: html

      $([*]){ The key value pair is [#] : [*] }.
    #  ____  _________________________________
    # /    \/                                 \
    #  loop         iterating expression
    # context    [#] ==> key , [*] ==> value


Or we can omit the $([*]) part since the root context is our current context and just write:

.. code-block:: html

    ${ The key value pair is [#] : [*] }


How this notation works is very similar to the PHP **foreach statement**. However, each time the view enters the ${ } loop it changes the key/value context so the meanings of [#] and [*] are different according to the position in the view (the loop level).

Let's take another example:

.. code-block:: php

    $data = array(
      "points" => array(array(10, 20), array(30, 40, 20), array(50, 60, 70)) ,
      "days" => array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday" )
    );


We can use our imagination and write a template which displays this data:

.. code-block:: html

    These are the points with X and Y values explicitly written out:
    $([points]){ Point at index [#] is ( [*.0] , [*.1] ) }

    These are the same points with all available components:
    $([points]){ Point at index [#] is ( $[ , ]{[*]} ) }

    These are the working days separated by a comma:
    $[, ]([days]){[*]}

This would produce to:

.. code-block:: html

    These are the points with X and Y values explicitly written out:
      Point at index 0 is ( 10 , 20 )
      Point at index 1 is ( 30 , 40 )
      Point at index 2 is ( 50 , 60 )

    These are the same points with all available components:
      Point at index 0 is ( 10 , 20 )
      Point at index 1 is ( 30 , 40 , 20 )
      Point at index 2 is ( 50 , 60 , 70 )

    These are the working days separated by a comma:
    Monday, Tuesday, Wednesday, Thursday, Friday


From this example we can cover three new features:

1. Iterating over a given key-value pair by defining the subcontext:

.. code-block:: html

    $([subcontext]) { ... }

2. The delimiter notation:

.. code-block:: html

    $[ delimiter ]([subcontext]){ ... }

3. The wrapping of the current value context:

.. code-block:: html

    /root
    $([*]) {
       /root/firstlevel items
       $([*]){
        /root/firstlevel/secondlevel items
       }
    }

The loop & Context
------------------

The best way to get comfortable with the looping and contexts would be through some examples. So let's summarize it with these:

.. code-block:: html

    # Pure iteration over current context.
    ${ item_pattern }

    # Pure iteration over current context + delimiter between outputs.
    $[delimiter]{ item_pattern }

    # Iteration over given subcontext within the current context.
    $([subcontext]){ item_pattern }
    $([*.subcontext]){ item_pattern }

    # Iteration over given subcontext within the current context + delimiter between outputs.
    $[delimiter]([subcontext]){ item_pattern }

    # Iteration of the upper level context (not applicable in root level).
    $([**]){ item_pattern }

Context operators
-----------------

1. Key operator / index operator: [#]
2. Value operator: [*]
3. Parent context value operator: [**]
4. Count operator: [~]
5. Key+1 operator: [#+]
6. (Modulo 2) operator: [#%2]
7. Reverse index operator : [!#]
8. Revere index +1 operator : [!#+]
9. Subcontext operator: [context.subcontext]
10. Level up context value operator: [\*\*], [\*\*.\*\*], ...


Data transformations
--------------------


Let's look at the following example:

Given a list of names, we want to print them all uppercased and comma separated.

.. code-block:: php

    <?php
    $data = array('John','Mike','Nicole','Jennifer');

The appropriate view template would be as follows:

.. code-block:: html

    $[, ]{[*:strtoupper]}

And the corresponding output would evaluate to:

.. code-block:: html

    JOHN, MIKE, NICOLE, JENNIFER

The lambda function in this example is the PHP built-in function **strtoupper**,

The syntax for data lambda-type data transformations on a context value is as follows:

.. code-block:: html

    [context<:lambda1<:lambda2<...<:lambdaN>>>>]

Let's view some more examples:

.. code-block:: html

    ['Y-m-d H:i:s':date]
    # output the date in the standard MySQL notation

    [*:md5:strtoupper]
    # output the MD5 hash uppercased

    [*:str_shuffle]
    # shuffle the letters of a string


Constants
---------

PHP defined constant values can be printed in the following manner:

1. By using the literal expression and the PHP lambda function constant

.. code-block:: html

    ${ ['MY_CONSTANT':constant] }

2. Using the shorthand operator @:

.. code-block:: html

    ${ [@MY_CONSTANT] }


**Example**

Data and the constant:

.. code-block:: php

    <?php
    define('SITEURL', '/path/to/siteroot');

    $data = array(
       array('id' => 123, 'title' => 'Apple'),
       array('id' => 453, 'title' => 'Orange'),
       array('id' => 517, 'title' => 'Lemon'),
    );

View template:

.. code-block:: html

    ${<a href="[@SITEURL]/item/[id].html">[title]</a>
    }

Output:

.. code-block:: html

    <a href="/path/to/siteroot/item/123.html">Apple</a>
    <a href="/path/to/siteroot/item/453.html">Orange</a>
    <a href="/path/to/siteroot/item/517.html">Lemon</a>


Conditional expressions and branching
-------------------------------------

Often, you'll want to display some data only if it satisfies a condition, or in some cases you'll end up with two or more options on how to display the data depending on it's value or other conditions.

A typical scenario is a navigation menu which indicates the current active link:

.. code-block:: php

    <?php
    // navigation.php
    // include in all page serving scripts: i.e. index.php, about.php, ...

    // get the name of the document being served
    $p = parse_url($_SERVER['REQUEST_URI']);
    $current_section = basename($p['path']);

    // define the structure of the navgation and indicate the active link
    $nav = array(
     'links' => array(
         'index.php' => 'Home'
       , 'about.php' => 'About'
       , 'contact.php' => 'Contact us'
     ),
     'current' => $current_section
    );


Example of a simple template for the navigation with the structure above.

.. code-block:: html

    <ul>
    $([links]){
     <li $?([**.current]==[#]){class="active"}><a href="/[#]">[*]</a></li>
    }</ul>

The branching syntax (IF and IF-ELSE):

IF:

.. code-block:: html

    $?(<condition>){<output-if-true>}


IF-ELSE:

.. code-block:: html

    $?(<condition>){<output-if-true>}{<output-if-false>}

Good news! You can write more complex logic with logical operators!

All logical operators are same as in PHP:

1. ! – NOT
2. && – AND
3. || – OR
4. == – EQUAL
5. != – NOT EQUAL
6. < - LESS THAN <= - LESS THAN OR EQUAL > – GREATER THAN
7. >= – GREATER THAN OR EQUAL

Also arithmetic:
+, -, \*, /, %

Lambda functions are called in the context as follows [context:lambda1:lambda2]...

All terms in the boolean expression are either a [context] or a 'literal'.
Let's look at a few examples:

.. code-block:: php

    <?php
    $data = array(
      'a' => true,
      'b' => true,
      'c' => false,
      'weather' => 'good',
      'number' => 10,
      'maybe_empty' => '',
      'elements' => array(10, 21, 30, 41, 50, 61, 70, 81, 90, 101)
    );

.. code-block:: html

    $?([a] && [b]){ A and B }

    $?(![a] && [c]) { not A and C  }

    $?([weather]=='good') { We're havin some good weather today } { Weather sucks today man }

    $?([number] > 5) { Over 5, to be exact it's a [number] } { Less than 5? Try again... }

    $?([maybe_empty]) { Well it's not empty } { Yep, it's empty }

    $?([weather]!='bad' && [number]>5) { It's your lucky day! }

    $?([weather::strtoupper] == 'GOOD') { Still having GOOD weather  }

    The elements from 2nd to 5th:
    ${
     $?([#+1]>=2 && [#+1]<=5) { [*] }
    }

    The elements with even values:
    ${
     $?([*]%2==0) { [*] }
    }

Escaping
--------

Special characters $, [, ] you want to output need to be escaped with a backslash as follows: \\$ , \\[ , \\].

You can also escape an entire block of text with the $<> quotation operator:

.. code-block:: html

    This is sensitive to special chars
    $<>This will always be printed as is.$<>
    This is sensitive to special chars

Notice that the **"literal" block** starts and ends with $<>

Comments
--------

You can place a comment into your template with the following syntax:

.. code-block:: html

    $/* This is a comment. It's only present in the template file. */

    Actual text being produced.
