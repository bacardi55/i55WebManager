<?php
namespace B55\I55WebManager\Entity;

class I55Config {
  private $name;
  private $workspaces;
  private $scratchpads;

  /**
   * Constructor.
   */
  public function __construct ($name, $nb_workspaces = 0) {
    $this->name = $name;
    $this->workspaces = array();
    $this->scratchpads = array();

    if ($nb_workspaces) {
      for ($i = 0; $i < $nb_workspaces; ++$i) {
        $this->workspaces[] = new I55Workspace($i);
      }
    }
  }

  /**
   * Getters/Setters
   */
  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  /* Workspaces */
  public function getWorkspaces($name = NULL) {
    $ret = $this->workspaces;

    if ($name != NULL) {
      foreach ($this->workspaces as $workspace) {
        if (strcmp($workspace->getName(), $name) === 0) {
          return $workspace;
        }
      }
    }

    return $this->workspaces;
  }

  public function setWorkspaces($workspaces) {
    $this->workspaces = $workspaces;
  }

  public function addWorkspace(I55Workspace $i55Workspace) {
    $this->workspaces[] = $i55Workspace;
  }

  public function removeWorkspace($workspace_name) {
    foreach ($this->workspaces as $key => $workspace) {
      if (strcmp($workspace_name, $workspace->getName()) === 0) {
        unset($this->workspaces[$key]);
      }
    }
    $this->workspaces = array_merge($this->workspaces);
  }

  /* Scratchpads */
  public function addScratchpad(I55Client $i55Client) {
    $this->scratchpads[] = $i55Client;
  }

  public function removeScratchpad($client_name) {
    foreach ($this->scratchpads as $id => $client) {
      if ($client->getName() == $client_name) {
        unset($this->scratchpads[$id]);
        $this->scratchpads = array_merge($this->scratchpads);
        return true;
      }
    }
  }

  public function getScratchpads($name = NULL) {
    if ($name) {
      foreach($this->scratchpads as $scratchpad) {
        if (strcmp($scratchpad->getName(), $name) === 0) {
          return $scratchpad;
        }
      }
    }
    return $this->scratchpads;
  }

  /* Clients */
  public function removeClient($workspace_name, $client_name) {
    foreach ($this->workspaces as $workspace) {
      if (strcmp($workspace_name, $workspace->getName()) === 0) {
        $workspace->removeClient($client_name);
      }
    }
  }

  public function addClient($workspace_name, I55Client $i55Client, $container_name = NULL) {
    foreach ($this->workspaces as $workspace) {
      if (strcmp($workspace->getName(), $workspace_name) === 0) {
        $workspace->addClient($i55Client, $container_name);
      }
    }
  }

  /**
   * Save methods
   */
  public function save() {
    $return = array(
      'name' => $this->name,
      'type' => 'I55Config',
      'workspaces' => array(),
      'scratchpads' => array()
    );

    for ($i = 0, $nb = count($this->workspaces); $i < $nb; ++$i) {
      $return['workspaces'][] = $this->workspaces[$i]->save();
    }

    for ($i = 0, $nb = count($this->scratchpads); $i < $nb; ++$i) {
      $return['scratchpads'][] = $this->scratchpads[$i]->save();
    }
    return $return;
  }
}
