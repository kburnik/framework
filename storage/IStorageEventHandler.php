<?

interface IStorageEventHandler extends IEventHandler {
  function onRead($variable);
  function onWrite($variable, $value);
  function onClear($variable);
  function onLoad($data);
  function onStore($data);
}

?>