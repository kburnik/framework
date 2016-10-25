<?php
include('../project.php');

class IndexView extends TemplateView {

  function main(){
    return "<h1>".Project::GetProjectTitle()."</h1>";
  }

}

return new IndexView( view('template.view.html') );

