<?

abstract class WebController extends Controller
{

  public function getUsedModels()
  {
    return array();
  }

  function css()
  {
    $resources = Project::getCurrent()->getResources();
    if (is_array($resources['css'])) {
      return "<!-- CSS -->\n".produce("$ {\t\t[*:css]\n }",$resources['css']);
    } else {
      return '<!-- NO css -->';
    }

  }

  function javascript()
  {
    $resources = Project::getCurrent()->getResources();
    if (is_array($resources['js'])) {
      return "<!-- Javascript -->\n".produce("$ {\t\t[*:javascript]\n }",$resources['js']);
    } else {
      return '<!-- NO javascript -->';
    }

  }


  function worktime()
  {
    return round(Application::ExecutionTime()*1000,2);
  }

}

?>