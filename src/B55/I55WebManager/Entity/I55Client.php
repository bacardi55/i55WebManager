<?php
namespace B55\I55WebManager\Entity;

class I55Client {
  protected $name;
  protected $command;
  protected $arguments;

  public function __construct($name, $command = NULL, $arguments = NULL) {
    $this->setName($name);

    if ($command && is_string($command)) {
      $this->command = $command;
      if ($arguments) {
        $this->setArguments($arguments);
      }
    }
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getArguments() {
    return $this->arguments;
  }

  public function setArguments($arguments) {
    $this->arguments = $arguments;
  }

  public function setCommand($command) {
    $this->command = $command;
  }

  public function getCommand() {
    return $this->command;
  }

  public function getFullCommand() {
    return $this->command . ' ' . $this->arguments;
  }

  public function save() {
    $return = array(
      'name' => $this->name,
      'command' => $this->command,
      'arguments' => $this->arguments,
      'type' => 'I55Client',
    );

    return $return;
  }

}
