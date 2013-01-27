<?php
namespace B55\I55WebManager;

use Symfony\Component\Yaml\Yaml;

use B55\I55WebManager\Entity\I55Config;
use B55\I55WebManager\Entity\I55Workspace;
use B55\I55WebManager\Entity\I55Container;
use B55\I55WebManager\Entity\I55Client;
use B55\I55WebManager\Entity\I55Configuration as I55Configuration;
use B55\I55WebManager\i3Msg as i3msg;

class I55WebManager {
  protected $file;
  protected $configs;
  protected $plain_config;
  protected $is_loaded = false;
  protected $configuration;

  public function __construct($file, $load = true) {
    $this->file = $file;
    $this->configs = array();
    $this->default_workspaces = array();
    $this->configuration = new I55Configuration();

    if (file_exists($file)) {
      $this->plain_config = Yaml::parse($file);
    }

    if ($load == true) {
      $this->load();
    }
  }

  /* Configs */
  public function getConfigs($config_name = NULL) {
    if (!$this->is_loaded) {
      $this->load();
    }
    $ret = $this->configs;

    if ($config_name) {
      for ($i = 0, $nb = count($this->configs); $i < $nb; ++$i) {
        if (strcmp($this->configs[$i]->getName(), $config_name) === 0) {
          $ret = $this->configs[$i];
        }
      }
    }
    return $ret;
  }

  public function setConfigs($configs) {
    $this->configs = $configs;
  }

  public function addConfig($config) {
    $this->configs[] = $config;
    $this->save();
  }

  public function removeConfig($config_name) {
    foreach ($this->configs as $key => $config) {
      if (strcmp($config->getName(), $config_name) === 0) {
        unset($this->configs[$key]);
      }
    }
    $this->configs = array_merge($this->configs);
    $this->save();
  }

  public function getConfigsNames() {
    return array_keys($this->plain_config['I55Config']);
  }
  public function createConfig($config_name = 'new') {
      return new I55Config($config_name);
  }


  /* Clients */
  public function addClient($config_name, $workspace_name, I55Client $i55Client, $container_name = NULL) {
    $flag = false;
    foreach ($this->configs as $config) {
      if (strcmp($config->getName(), $config_name) === 0) {
        $config->addClient($workspace_name, $i55Client, $container_name);
        $this->save();
      }
    }
  }

  public function removeClient($config_name, $workspace_name, $client_name) {
    if ($config = $this->getConfigs($config_name)) {
      $config->removeClient($workspace_name, $client_name);
      $this->save();
    }
  }


  /* Workspaces */
  public function setWorkspace($config_name, I55Workspace $i55Workspace, $workspace_to_replace = NULL) {
    $flag = false;
    foreach ($this->configs as $config) {
      if (strcmp($config->getName(), $config_name) === 0) {
        if ($workspace_to_replace !== NULL) {
          $nb_workspace = count($config->getWorkspaces());
          $i = 0;
          foreach ($config->getWorkspaces() as $workspace) {
            ++$i;
            if (strcmp($workspace->getName(), $workspace_to_replace) === 0) {
              $workspace->setName($i55Workspace->getName());
              $flag = true;
            }
          }
        }

        if (($workspace_to_replace === NULL)|| ($i == $nb_workspace && !$flag)) {
          $config->addWorkspace($i55Workspace);
        }
      }
    }
    $this->save();
  }

  public function removeWorkspace($config_name, $workspace_name) {
    if ($config = $this->getConfigs($config_name)) {
      $config->removeWorkspace($workspace_name);
      $this->save();
      return;
    }
  }


  /* Configuration */
  public function getConfiguration() {
    return $this->configuration;
  }

  public function setConfiguration(I55Configuration $i55Configuration) {
    $this->configuration = $i55Configuration;
  }

  /**
   * Return if the config exists or not
   *
   * @return Boolean
   *   False if the config existed
   *   True if the config doen't exists
   */
  public function is_new() {
    if (!$this->is_loaded) {
      $this->load();
    }

    if (!count($this->configs)) {
      return true;
    }
    return false;
  }

  public function has_configuration () {
    if (!$this->is_loaded) {
      $this->load();
    }

    return (count($this->configuration));
  }



  /**
   * Save method
   */
  public function save() {
    $yaml = $this->generateYaml();
    $filename = $this->file;

    if (false === file_put_contents($filename, utf8_encode($yaml), LOCK_EX)) {
      // Add exception.
      die('Error saving the file, make sure that a file can be created in the folder src/B55/Resources');
    }
    $this->plain_config = Yaml::parse($filename);
    return true;
  }

  /**
   * Load method
   */
  public function load($config_name = NULL) {
    $configs = $this->plain_config['I55Config'];
    if (!is_array($configs)) {
      return;
    }
    foreach ($configs as $name => $config) {
      $i55Config = new I55Config($name);
      if (array_key_exists('workspaces', $config)) {
        $workspaces = $config['workspaces'];
        for ($i = 0, $nb = count($workspaces); $i < $nb; ++$i) {
          $workspace = new I55Workspace($workspaces[$i]['name']);
          $workspace->setDefaultLayout($workspaces[$i]['default_layout']);

          // In the next version, this part will become way more
          // complicated (containers in containers, …).
          if (array_key_exists('containers', $workspaces[$i])) {
            $containers = $workspaces[$i]['containers'];

            for ($j = 0, $nbc = count($containers); $j < $nbc; ++$j) {
              $container = new I55Container($containers[$j]['name']);
              if (array_key_exists('clients', $containers[$j])) {
                $clients = $containers[$j]['clients'];

                for ($k = 0, $nbcl = count($clients); $k < $nbcl; ++$k) {
                  $cl = $clients[$k];
                  $client = new I55Client($cl['name']);
                  if (array_key_exists('command', $cl)) {
                    $client->setCommand($cl['command']);
                  }
                  if (array_key_exists('arguments', $cl)) {
                    $client->setArguments($cl['arguments']);
                  }
                  $container->addClient($client);
                }
                $workspace->addContainer($container);
              }
            }
            $i55Config->addWorkspace($workspace);
          }
        }
      }

      if (array_key_exists('scratchpads', $config)) {
        $scratchpads = $config['scratchpads'];
        for($i = 0, $nb = count($scratchpads); $i < $nb; ++$i) {
          $i55Client = new I55Client($scratchpads[$i]['name']);
          if (array_key_exists('command', $scratchpads[$i])) {
            $i55Client->setCommand($scratchpads[$i]['command']);
          }
          if (array_key_exists('arguments', $scratchpads[$i])) {
            $i55Client->setArguments($scratchpads[$i]['arguments']);
          }
          $i55Config->addScratchpad($i55Client);
        }
      }

      $this->configs[] = $i55Config;
    }

    if (array_key_exists('configuration', $this->plain_config)) {
      $i55Configuration = new I55Configuration();
      if (array_key_exists('default_workspaces', $this->plain_config['configuration'])) {
        $d_workspaces = $this->plain_config['configuration']['default_workspaces'];
        for ($i = 0, $nb = count($d_workspaces); $i < $nb; $i++) {
          $i55Workspace = new I55Workspace($d_workspaces[$i]['name']);
          $i55Configuration->addDefaultWorkspace($i55Workspace);
        }
      }
      $this->configuration = $i55Configuration;
    }

    $this->is_loaded = true;
  }

  /**
   * Run method
   */
  public function run($name, i3Msg\i3MsgInterface $i3Msg, $file) {
    $config = $this->getConfigs($name);
    return $i3Msg->run($config, $file);
  }


  /* Private Methods */
  /**
   * Generate yaml
   */
  private function generateYaml() {
    $configs = array();
    for ($i = 0, $nb = count($this->configs); $i < $nb; ++$i) {
      $configs[$this->configs[$i]->getName()] = $this->configs[$i]->save();
    }
    $workspaces = array();
    for ($i = 0, $nb = count($this->default_workspaces); $i < $nb; ++$i) {
      $workspaces[$this->default_workspaces[$i]->getName()]
        = $this->default_workspaces[$i]->save();
    }

    $configs = array(
      'I55Config' => $configs,
      'type' => 'I55Config',
      'configuration' => $this->configuration->save(),
    );
    $yaml = Yaml::dump($configs);
    return $yaml;
  }
}
