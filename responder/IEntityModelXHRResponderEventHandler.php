<?

interface IEntityModelXHRResponderEventHandler {
  function onInsert($responder, $entityModel, $data, $result);
  function onUpdate($responder, $entityModel, $data, $result);
  function onDelete($responder, $entityModel, $data, $result);
  function onCommit($responder, $entityModel, $data, $result);
}

?>