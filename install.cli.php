<?
// PHP commandline install utility for creating a new project using Framework

include_once('base/Base.php');


echo "Creating project directories!\n";

$directories = Project::GetDefaultProjectDirectoryStructure();		
$result = Project::createProjectDirectoryStructure(getcwd(), $directories);

$result = ($result) ? 'OK' : 'error!';
echo "Result: $result\n";

die();
//		
		

$workingDirectory = getcwd();
$frameworkDirectory = dirname(__FILE__);
$frameworkVersion = '1.0';

// todo place somewhere else
function readinput($prompt, $default = '') {
    while(!isset($input)) {
        echo $prompt;
		echo "\n[Default: $default] >> ";
        $input = trim(fgets(STDIN));
        if(empty($input) && !empty($default)) {
            $input = $default; 
        } 
    } 
    return $input; 
} 


echo "\n";
echo "THIS IS AN INCOMPLETE SCRIPT!!!\n";
echo "------------------------------------------------------------\n";
echo "FRAMEWORK v $frameworkVersion by Kristijan Burnik\n";
echo "------------------------------------------------------------\n";

echo "Used framework will be located in: $frameworkDirectory\n";
echo "You'll be creating a new project rooted at $workingDirectory\n";

echo "------------------------------------------------------------\n";



$projectTitle = readinput("Enter the project's full title: ", 'EmptyProject');
echo "Project will be Titled '{$projectTitle}'\n";
echo "\n";

$projectName = readinput("Enter the project's code name (like a file name): ", strtolower(keyword_name($projectTitle)));
echo "Project's code name is '{$projectName}'\n";
echo "\n";

$projectAuthorName = readinput("Enter the project's author name: ", "Kristijan Burnik");
echo "Project's author is '{$projectAuthorName}'\n";
echo "\n";

$projectTimezone = readinput("Enter the project's time zone: ", "Europe/Zagreb");
echo "Project's time zone is '{$projectTimezone}'\n";
echo "\n";


?>