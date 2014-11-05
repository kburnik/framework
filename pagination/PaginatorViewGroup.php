<?


abstract class PaginatorViewGroup implements IPaginatorViewGroup {
  
  function getRangeView($start,$limit,$data) {    
    $out = $this->getRangeHeaderView($start,$limit);
    foreach ($data as $item) {
        $out .= $this->getItemView($item);
    }
    $out .= $this->getRangeFooterView($start,$limit);
    return $out;
  }
  
  function getNavigationTemplateView($partsArray) {
    $out = implode("",$partsArray);
    return $out;
  }
  
  
  
}
?>