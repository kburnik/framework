<?php

// main class used for distinguishing from projects
class Project extends Base {

  ///////////////  static

  private static $storage;
  private static $initialized = false;
  private static $currentProject = null;

  public static function __set_state($data) {
    return new Project($data['projectName'],
                       $data['projectTitle'],
                       $data['projectAuthor'],
                       $data['projectRoot'],
                       false);
  }


  public static function Init() {
    if (self::$initialized)
      return;
  }

  /*
   * Returns default structure a project should use
   * todo: Deprecate this soon! Create a template directory instead and
   * duplicate it for a project!
   * @return array describing the directory structure
  */
  public static function GetDefaultProjectDirectoryStructure() {
    return array(
        'autohandlers' => array()
      , 'handlers' => array()
      , 'gen' => array( 'template' => array() )
      , 'cache' => array()
      , 'model' => array( '.include' => null )
      , 'public' => array(
          'css' => array()
        , 'js' => array()
        , 'images' => array()
        , 'download' => array()
        , 'view' => array( 'template.view.html' => null )
        , 'TemplateView.php' => null
        , 'index.php' => null
      )
      , 'project.php' => null
      , '.include' => null
    );
  }


  /*
   * Create directories starting with a parent directory and an array structure
   * todo: place this function in a lower level class!
   * @return boolean
  */
  public static function CreateProjectDirectoryStructure($parentDirectory, $structure) {
    foreach ($structure as $entryName => $substructure) {
      $entryFullPath = $parentDirectory.'/'.$entryName;
      if ($substructure === null) {
        if (!file_exists( $entryFullPath )) touch( $entryFullPath );
      } else {
        if (file_exists($entryFullPath) || mkdir( $entryFullPath )) {
          $ok = self::createProjectDirectoryStructure($entryFullPath, $substructure );
          if (!$ok) {
            return false;
          }
        } else {
          return false;
        }
      }
    }
    return true;
  }

  /*
   * Create directories for the project instance
   * @return boolean
  */
  public function createDirectories() {
    $directories = self::GetDefaultProjectDirectoryStructure();
    return self::createProjectDirectoryStructure($this->getDir(''), $directories);
  }


  public static function Register($project) {
    self::Init();
    self::$currentProject = $project;

    return $project;
  }

  public static function GetCurrent() {
    if (!isset(self::$currentProject) || self::$currentProject == null || (! self::$currentProject instanceof Project )) {
      throw new Exception('No current Project is registered!');
    }
    return self::$currentProject;
  }

  // shortcut for getting current project's root
  public static function GetProjectRoot() {
    return self::GetCurrent()->getRoot();
  }

  public static function GetProjectDir($subdir = '') {
      return self::GetCurrent()->getDir($subdir);
  }

  public static function GetProjectFile() {
    $project_file = self::GetCurrent()->getDir('').'/project.php';
    if (!file_exists($project_file)) {
      throw new Exception('Cannot find project file at ' . $project_file);
    }
    return $project_file;
  }

  public static function GetProjectTitle() {
    return self::GetCurrent()->getTitle();
  }


  public static function GetProjectSyncStorage() {
    return self::GetCurrent()->getSyncStorage();
  }

  public static function SetProjectContextDir($dir) {
    return Project::GetCurrent()->setContextDir($dir);
  }

  public static function GetProjectContextDir($subdir = '' ) {
    return Project::GetCurrent()->getContextDir($subdir);
  }

  // resources to include, i.e. javascript and css
  private $resources = array();
  public function includeResources( $resourceArray  ) {
    // include resources but no more than once per each context
    foreach ($resourceArray as $context  => $res) {
      $this->resources[$context] =  array_unique(
        array_merge((array)$this->resources[$context],$res) );
    }

  }

  public function getResources() {
    return $this->resources;
  }


  // shortcut for getting current project's queried data provider;

  public static function GetQDP() {
    return self::GetCurrent()->getQueriedDataProvider();
  }


  public static function Create($projectName,
                                $projectTitle,
                                $projectAuthor,
                                $projectRoot,
                                $projectTimeZone) {
    return self::Register(new Project($projectName,
                                      $projectTitle,
                                      $projectAuthor,
                                      $projectRoot,
                                      $projectTimeZone));
  }

  // call when needing to run test cases in project context
  public static function CreateTestCase( $projectName = "Framework" )
  {
    self::Create(
      /* name */     "testcase",
      /* title */   "{$projectName} TestCase",
      /* author */   "Kristijan Burnik",
      /* root */     getcwd(),
      /* timezone */   "Europe/Zagreb"
    );
  }

  ///////////////   object

  private $projectName;
  private $projectTitle;
  private $projectAuthor;
  private $projectRoot;
  private $projectTimeZone;
  private $projectContextDir;

  // create new project
  public function __construct($projectName,
                              $projectTitle,
                              $projectAuthor,
                              $projectRoot,
                              $projectTimeZone,
                              $register = true) {
    $this->projectName = $projectName;
    $this->projectTitle = $projectTitle;
    $this->projectAuthor = $projectAuthor;
    $this->projectRoot = $projectRoot;
    $this->projectTimeZone = $projectTimeZone;


    date_default_timezone_set($projectTimeZone);
    if ($register)
      self::Register($this);

    $this->fillAutoEventHandlerMap();
  }

  public function getName() {
    return $this->projectName;
  }

  public function getTitle() {
    return $this->projectTitle;
  }

  public function getAuthor() {
    return $this->projectAuthor;
  }

  public function getRoot() {
    return $this->projectRoot;
  }

  public function getDir($subdir = '') {
    return $this->projectRoot.$subdir;
  }

  public function getTimeZone() {
    return $this->projectTimeZone;
  }

  private $queriedDataProvider = null;
  public function setQueriedDataProvider($queriedDataProvider) {
    $this->queriedDataProvider = $queriedDataProvider;
  }

  public function getQueriedDataProvider() {
    if ($this->queriedDataProvider === null ||
        !($this->queriedDataProvider instanceof IQueriedDataProvider ) ) {
      throw new Exception('Queried Data Provider not bound to Project!');
    }
    return $this->queriedDataProvider;
  }


  public function setContextDir($dir) {
    $this->projectContextDir = $dir;
  }

  public function getContextDir($subdir = '') {
    return $this->projectContextDir . '/' . $subdir ;
  }


  private $autoEventHandlerMap;

  // Browses through the handler directory and map known model interfaces and
  // implementing handlers.
  private function fillAutoEventHandlerMap() {
    $directory = $this->getDir('/autohandlers');

    if ( !file_exists( $directory ) )
      return;

    $d = dir( $directory );

    while (false !== ($entry = $d->read())) {
      if ($entry[0] != '.' && end(explode('.',$entry)) == 'php' ) {
        $path = $d->path."/".$entry;
        $current_classes = get_declared_classes();
        include_once( $path );
        $new_classes = array_diff( get_declared_classes(), $current_classes  );
        foreach ($new_classes as $new_class) {
          $implementations =  class_implements($new_class);
          foreach ($implementations as $new_interface) {
            $this->autoEventHandlerMap[ $new_interface ][] = $new_class;
          }
        }
      }
    }
  }

  // Binds all classes implementing the event handler interfaces.
  public function bindProjectAutoEventHandlers( $model ) {
    $interfaceName = (string) $model->getEventHandlerInterface();
    if (!defined('SKIP_PROJECT_LOGGING'))
      Console::WriteLine("Project :: Starting auto event handler bind for model ".get_class($model)." with interface handler " . $interfaceName);

    if (is_array( $this->autoEventHandlerMap[$interfaceName] ) ) {
      foreach ( $this->autoEventHandlerMap[$interfaceName] as $handlerClass  ) {
        $model->addEventHandler(new $handlerClass());
        if (!defined('SKIP_PROJECT_LOGGING'))
          Console::WriteLine("Project :: Binding event handler $handlerClass to model " . get_class($model) );
      }
    } else {
      if (!defined('SKIP_PROJECT_LOGGING'))
        Console::WriteLine('Project :: Warning! Could not find any handlers for interface ' . $interfaceName);
    }

  }

  // synchronous storage

  private $syncStorage;
  public function getSyncStorage() {
    if (!isset($this->syncStorage)) {
      $this->syncStorage = new SyncStorage($this->getRoot().'/gen/project.sync.storage.php');
    }
    return $this->syncStorage;
  }


  // find and include the project
  public function Resolve($startDirectory = null, $config_only = false) {
    if ($startDirectory === null)
      $startDirectory = getcwd();

    $inclusion_candidates =
        $config_only ?
        array("project-settings.php", "project-config.php") :
        array("project.php");

    $startDirectory = str_replace("\\", "/", $startDirectory);

    $parts = explode("/", $startDirectory);

    while (!empty($parts)) {
      $dir = implode("/", $parts);
      foreach ($inclusion_candidates as $candidate_basename) {
        $project_file = "$dir/$candidate_basename";
        if (file_exists($project_file)){
          chdir($dir);
          include_once($project_file);

          return true;
        }
      }
      array_pop($parts);
    }

    return false;
  }
}

