<?php
namespace B55\I55WebManager\I3Msg;

use B55\I55WebManager\I3Msg\I3MsgInterface;
use B55\I55WebManager\Entity\I55Workspace;
use B55\I55WebManager\Entity\I55Client;

class I55Msg implements I3MsgInterface {
    public function run($i55Config, $file = '') {
        $output = '#!/bin/bash' . "\n\n";
        $workspaces = $i55Config->getWorkspaces();

        foreach ($workspaces as $wk_id => $workspace) {
            $output .= $this->goto_workspace($workspace);

            if ($workspace->getDefaultLayout() != 'default') {
                $output .= $this->set_layout($workspace->getDefaultLayout());
            }

            $containers = $workspace->getContainers();
            foreach ($containers as $ct_id => $container) {
                $clients = $container->getClients();
                foreach ($clients as $client) {
                    $output .= $this->open_client($client);
                }
            }
        }
        $scratchpads = $i55Config->getScratchpads();
        foreach ($scratchpads as $sc_id => $scratchpad) {
            $output .= $this->open_scratchpad($scratchpad);
        }

        $this->write_bash_script($output, $file);

        return $output;
    }

    public function goto_workspace(I55Workspace $i55Workspace) {
        //$command = $this->get_goto_command($i55Workspace);
        $command = $this->get_goto_command($i55Workspace) . "\n";
        // Make that configurable.
        //sleep(0.1);
        $command .= "sleep 0.1\n";

        return $command;
    }

    public function open_client(I55Client $i55Client) {
        $cmd = '';
        if ($cmd = $i55Client->getFullCommand()) {
            $cmd = escapeshellcmd($cmd);
            //exec('nohup ' . $cmd . ' > /dev/null 2>&1 &');
            // Make that configurable.
            //sleep(2);

            $cmd = 'nohup ' . $cmd . "\nsleep 2\n";
        }
        return $cmd;
    }

    public function open_scratchpad(I55Client $i55Client) {
        // TODO: http://faq.i55wm.org/question/954/open-window-as-a-scratchpad/
        return;
    }

    public function set_layout($layout, $workspace = NULL) {
        $cmd = 'i3-msg layout ' . $layout;
        //exec($cmd);
        //sleep(0.5);

        $cmd .= "\nsleep 0.5\n";
        return $cmd;
    }

    /* Protected methods */

    protected function get_goto_command(I55Workspace $i55Workspace) {
        return 'i3-msg workspace ' . $i55Workspace->getName() . ';';
    }

    protected function write_bash_script($script, $file = '') {
        $ret = file_put_contents($file, $script);
        if ($ret === false) {
            return $ret;
        }
        else {
            return true;
        }
    }
}
