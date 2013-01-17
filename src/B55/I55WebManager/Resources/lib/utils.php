<?php

function getYamlFilePathFromApp($app) {
  return $app['i55WebManager']['default']['path']
    . $app['i55WebManager']['default']['name'];
}

function getI55Layouts() {
  // TODO: read this from I55
  return array(
    'default' => 'default',
    'tabbed' => 'tabbed',
    'stacking' => 'stacking'
  );
}
