<?phpinterface IViewProviderFactory {  public function viewProviderExists($viewProviderKey);  public function getViewProvider($viewProviderKey);}
