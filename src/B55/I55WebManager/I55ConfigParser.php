<?php
namespace B55\I55WebManager;

//use B55\Entity;
use B55\I55WebManager\Entity\I55Config;
use B55\I55WebManager\Entity\I55Workspace;

class I55ConfigParser {
  protected $filename;
  protected $workpaces;

  public function __construct($filename) {
    if (is_file($filename) && is_readable($filename)) {
      $this->filename = $filename;
      $this->workspaces = array();
    }
  }

  public function getFilename() {
    return $this->filename;
  }

  public function setFilename($filename) {
    $this->filename = $filename;
  }

  public function parse() {
    $file_handle = fopen($this->filename,  "r");
    while (!feof($file_handle)) {
      $line = fgets($file_handle);
      $matches = array();
      if (preg_match('#move workspace (.+)#i', $line, $matches)) {
        $workspace_name = $matches[1];
        $this->workspaces[] = new i55Workspace($workspace_name);
      }
    }
    fclose($file_handle);
    return $this->workspaces;
  }
}
