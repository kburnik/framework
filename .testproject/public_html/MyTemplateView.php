<?

abstract class TemplateView extends HTMLView {

  protected $context, $categories;

  public function title() {
    return Project::GetProjectTitle();
  }

  public function meta_description() {
    return "Project description";
  }

  function initialize() {

  }


  // the models used for this view = used for resource handling
  public function getUsedModels() {
    return array_merge(parent::getUsedModels(),
      array(

      )
    );
  }


  public function navigation() {

  }


}


?>