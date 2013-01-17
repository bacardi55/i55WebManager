<?php
namespace B55\I55WebManager\Entity;

class I55Configuration {
  protected $default_workspaces;

  public function __construct() {
    $this->default_workspaces= array();
  }

  public function getDefaultWorkspaces() {
    return $this->default_workspaces;
  }

  public function setDefaultWorkspaces($workspaces) {
    $this->default_workspaces = $workspaces;
  }

  public function addDefaultWorkspace(I55Workspace $i55Workspace) {
    $this->default_workspaces[] = $i55Workspace;
  }

  public function save() {
    $df = array();
    foreach ($this->default_workspaces as $wk) {
      $df[] = $wk->save();
    }

    return array(
      'default_workspaces' => $df
    );
  }
}
