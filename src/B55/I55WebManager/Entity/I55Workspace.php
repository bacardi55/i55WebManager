<?php
namespace B55\I55WebManager\Entity;

use B55\I55WebManager\Entity\I55Client as I55Client;

require_once __DIR__ . '/../Resources/lib/utils.php';

class I55Workspace {
  protected $containers;
  protected $name;
  protected $defaultLayout;

  public function __construct($name) {
    $this->setName($name);
    $this->defaultLayout = 'default';
    $this->containers = array();
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  /* Default Layout */
  public function getDefaultLayout() {
    return $this->defaultLayout;
  }

  public function setDefaultLayout($defaultLayout) {
    if (array_key_exists($defaultLayout, getI55Layouts())) {
      $this->defaultLayout = $defaultLayout;
    }
  }

  /* Containers */
  public function getContainers() {
    return $this->containers;
  }

  public function setContainers($containers) {
    $this->containers = $containers;
  }

  public function addContainer(I55Container $i55Container) {
    $this->containers[] = $i55Container;
  }

  /* Clients */
  public function getClient($client_name) {
    foreach ($this->containers as $container) {
      $i55Client = $container->getClients($client_name);

      if ($i55Client instanceof I55Client) {
        return $i55Client;
      }
    }
    return false;
  }

  public function removeClient($client_name) {
    foreach ($this->getContainers() as $container) {
      $container->removeClient($client_name);
    }
  }

  public function addClient(I55Client $i55Client, $container_name = NULL) {
    $nb_containers = count($this->getContainers());
    // This workspace is a virgin, don't need to do more.
    if (!$nb_containers) {
      $i55Container = new I55Container();
      $i55Container->addClient($i55Client);
      $this->addContainer($i55Container);
      return;
    }
    else {
      if (!$container_name) {
        $container = current($this->containers);
        // TODO: Check container as child later.
        $container->addClient($i55Client);
        return;
      }
      foreach ($workspace->getContainers() as $container) {
        if (strcmp($container->getName(), $container_name) === 0) {
          $container->addClient($i55Client);
          return;
        }
      }
    }
  }

  public function getNumberOfClients() {
    $nb = 0;
    foreach ($this->containers as $container) {
      $nb += count($container->getClients());
    }

    return $nb;
  }

  public function getClientsNames() {
    $ret = '';
    foreach ($this->containers as $container) {
      foreach ($container->getClients() as $client) {
        $ret .= $client->getName() . ', ';
      }
    }
    return substr($ret, 0, -2);
  }

    public function getNbTotalClients() {
        $total = 0;
        for ($i = 0, $nb = count($this->containers); $i < $nb; ++$i) {
            $total += count($this->containers[$i]->getClients());
        }
        return $total;
    }

  /**
   * Save methods
   */
  public function save() {
    $containers = array();
    for ($i = 0, $nb = count($this->containers); $i < $nb; ++$i) {
      $containers[] = $this->containers[$i]->save();
    }

    $return = array(
      'name' => $this->name,
      'type' => 'I55Workspace',
      'default_layout' => $this->defaultLayout,
      'containers' => $containers
    );

    return $return;
  }
}
