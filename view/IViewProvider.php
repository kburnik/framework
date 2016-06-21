<?php

interface IViewProvider {
  function containsTemplate($viewKey) ;
  function getTemplate($viewKey) ;
  function getView($viewKey, $data);
  function getResources($viewKey, $section);
}
