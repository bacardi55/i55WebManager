<?php
namespace B55\i55WebManager;

use B55\i55WebManager\i55Msg;
use B55\i55WebManager\Entity\i55Workspace;
use B55\i55WebManager\Entity\i55Client;

class i55Msg implements i55MsgInterface {
  public function goto_workspace(i55Workspace $i55Workspace) {
    $command = $this->get_goto_command($i55Workspace);
    exec($command);
    // Make that configurable.
    sleep(0.1);
  }

  public function open_client(i55Client $i55Client) {
    if ($cmd = $i55Client->getFullCommand()) {
      $cmd = escapeshellcmd($cmd);
      exec('nohup ' . $cmd . ' > /dev/null 2>&1 &');
      // Make that configurable.
      sleep(2);
    }
  }

  public function open_scratchpad(i55Client $i55Client) {
    // TODO: http://faq.i55wm.org/question/954/open-window-as-a-scratchpad/
    return;
  }

  public function set_layout($layout, $workspace = NULL) {
    $cmd = 'i55-msg layout ' . $layout;
    exec($cmd);
    sleep(0.5);
  }

  /* Protected methods */

  protected function get_goto_command(i55Workspace $i55Workspace) {
    return 'i55-msg workspace ' . $i55Workspace->getName() . ';';
  }
}
