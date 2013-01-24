<?php
namespace B55\I55WebManager\Entity;

use B55\I55WebManager\Entity\I55Client as I55Client;

class I55Container {
  protected $clients;
  protected $containers;
  protected $layout;

  public function __construct($layout = NULL) {
    if ($layout) {
      $this->setLayout($layout);
    }
    $this->clients = array();
    $this->containers = array();
  }

  public function getClients($client_name = NULL) {
    $ret = $this->clients;
    if ($client_name && is_string($client_name)) {
      foreach ($this->clients as $client) {
        if (strcmp($client->getName(), $client_name) === 0) {
          $ret = $client;
        }
      }
    }
    return $ret;
  }

  public function setClients($clients) {
    $this->clients = $clients;
  }

    public function createClient() {
        return new I55Client('new');
    }
  public function getContainers() {
    return $this->containers;
  }

  public function setContainers($containers) {
    $this->containers = $containers;
  }

  public function getLayout() {
    return $this->layout;
  }

  public function setLayout($layout) {
    $this->layout = $layout;
  }

  public function save() {
    $clients = array();
    for ($i = 0, $nb = count($this->clients); $i < $nb; ++$i) {
      $clients[] = $this->clients[$i]->save();
    }

    $containers = array();
    for ($i = 0, $nb = count($this->containers); $i < $nb; ++$i) {
      $containers[] = $this->containers[$i]->save();
    }

    $return = array(
      'name' => $this->layout,
      'type' => 'I55Container',
      'clients' => $clients,
      'containers' => $containers,
    );

    return $return;
  }

  public function addContainer(I55Container $i55Container) {
    $this->containers[] = $i55Container;
  }

  public function addClient(I55Client $i55Client) {
    $this->clients[] = $i55Client;
  }

  public function removeClient($client_name) {
    foreach ($this->clients as $key => $client) {
      if (strcmp($client->getName(), $client_name) === 0) {
        unset($this->clients[$key]);
      }
    }
    $this->clients = array_merge($this->clients);
  }
}
