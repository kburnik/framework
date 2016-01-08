Getting started
===============

If you're planning on starting a new project using **Framework**, continue
reading on starting a :ref:`new-project`. If you wish to utilize the framework in an existing project skip to the
:ref:`existing-project` section below.

.. _new-project:

New project
===========

The recommended way to start a project is to check out the framework scaffold
project and continue from there.

.. code-block:: bash

    git clone https://github.com/kburnik/framwork-scaffold

You can find more documentation on setting up the scaffold in the project docs.

.. _existing-project:

Existing project
================

Start by cloning the repository to a directory in your project. A good idea
would be to have a **third_party** directory and check it out there.

.. code-block:: bash
   cd /path/to/project
   mkdir third_party
   cd third_party
   git clone https://github.com/kburnik/framework

Depending on where you want the framework to be available, you will need
to create the project-settings.php and project.php files in the same directory.

project-settings.php
--------------------
.. code-block:: php

    <?php
    define('PROJECT_DIR', dirname(__FILE__));
    define('PATH_TO_FRAMEWORK', constant('PROJECT_DIR') . '/third_party/framework');

    # REGIONAL.
    define('PROJECT_LANGUAGE', 'hr');
    define('PROJECT_TIMEZONE', 'Europe/Zagreb');

    # NAME.
    define('PROJECT_NAME', 'myapp');
    define('PROJECT_TITLE', 'My App');
    define('PROJECT_DESCRIPTION', 'An empty project for scaffolding.');

    # AUTHOR.
    define('PROJECT_AUTHOR', 'John Doe');

    // Optional: if you want to use Framwork with a MySQL DB.
    # DB.
    define('PROJECT_MYSQL_USERNAME', 'myapp');
    define('PROJECT_MYSQL_PASSWORD', 'password');
    define('PROJECT_MYSQL_TEST_DATABASE', '');

project.php
-----------

.. code-block:: php

    <?php
    include_once(dirname(__FILE__) . '/project-settings.php');
    include_once(PATH_TO_FRAMEWORK . '/base/Base.php');

    $project = Project::Create(constant('PROJECT_NAME'),
                               constant('PROJECT_TITLE'),
                               constant('PROJECT_AUTHOR'),
                               constant('PROJECT_DIR'),
                               constant('PROJECT_TIMEZONE'));

    // Optional: if you want to use framework with a MySQL DB.
    if (defined('PROJECT_MYSQL_DATABASE')) {
      $mysql = new MySQLProvider('localhost',
                                 constant('PROJECT_MYSQL_USERNAME'),
                                 constant('PROJECT_MYSQL_PASSWORD'),
                                 constant('PROJECT_MYSQL_DATABASE'));
      $project->setQueriedDataProvider($mysql);
      SurogateDataDriver::SetRealDataDriver(new MySQLDataDriver());
      $mysql->connect();
    }

    $application = Application::getInstance();
    $application->Start();


Once you have the project.php file, you can include it in any of your scripts
which want to use the Framework features.

This will register an autoloader class so you can reference other framework
and your project classes without needing to include them.

The naming convention
---------------------

Framework and projects built on top of it follow a simple naming convention
which helps finding and including the class when it's first requested:

 ClassName.php

.. code-block:: php

   <?php
   class ClassName {}


How autoloading works
---------------------

Framework will automatically try to include a class if it's not present in the
execution environment. However, for the autoloader to know where to search you
need to place an **.include** starting from your project root (PROJECT_DIR).

Example:

- project_dir/.include
- project_dir/project.php
- project_dir/project-settings.php
- project_dir/module/.include
- project_dir/module/MyModuleClass.php
- project_dir/module/submodule/FramworkAutoloaderCannotSeeMe.php
- project_dir/public_html/index.php


In the index.php you just need to include the project.php and MyModuleClass will
be available to the script as soon as you use it.

  project_dir/public_html/index.php

.. code-block:: php

    <?php
    include_once(dirname(__FILE__) . '/../project.php');

    # This will trigger the Framwork autoloader.
    $module = new MyModuleClass();
    $module->doStuff();

    echo $module->getResults();

Framwork will start in the
project root (project_dir) and recursively look for .include in every directory
until it finds MyModuleClass.php.

Notice how our submodule direcotory does not have an .include file.
This will cause the autoloader to skip the entire submodule directory when
searching for the class FrameworkAutoLoaderCannotSeeMe and produce a regular
PHP error when referencing a non-existing class.

You can either include the class yourself or add the .include file to remedy
this.

