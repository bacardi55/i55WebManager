<?php
namespace B55\I55WebManager\i3Msg;

use B55\I55WebManager\Entity\I55Workspace;
use B55\I55WebManager\Entity\I55Client;

interface I3MsgInterface {
  public function goto_workspace(I55Workspace $i55Workspace);
  public function open_client(I55Client $i55Client);
  public function open_scratchpad(I55Client $i55Client);
  public function set_layout($layout, $workspace = NULL);
}
