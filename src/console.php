<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

$console = new Application('i55 Cli Manager', '0.1');

$console
    ->register('i55Cli:run')
    ->setDefinition(array(
        new InputOption('dump', '', InputOption::VALUE_NONE, 'Print the bash script in the console directly'),
        new InputOption('config_path', '', InputOption::VALUE_OPTIONAL, 'Path to your yaml config file'),
        new InputOption('export_path', '', InputOption::VALUE_OPTIONAL, 'Where to write the final bash script'),
    ))
    ->addArgument(
        'config_name',
        InputArgument::REQUIRED
    )
    ->setDescription('Start an i55Config')
    ->setHelp('Usage :<info>php console i55CliManager:start [config_name] --verbose')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $i55wm = $app['I55wm'];
        $config_name = $input->getArgument('config_name');
        $I3Msg = new $app['I3Msg']['class']();

        if (!in_array('B55\I55WebManager\I3Msg\I3MsgInterface', class_implements($I3Msg))) {
            $output->writeln('<error>Error while loading the I3Msg implementator, check your prod.php file for an $app["I5Msg"] entry');
        }

        if ($i55wm->is_new()) {
            $output->write("\n<error>You don't have a configuration yet, please make one via the web interface
                            (there will be an cli interface for that one day…).</error>\n\n");
            return;
        }

        if (in_array($config_name, $i55wm->getConfigsNames())) {
            $config_path = $input->getOption('config_path');
            if ($config_path && is_readable($config_path)) {
                $i55wm->setFile($config_path, true);
            }
            $export_path = $input->getOption('export_path');
            if (!$export_path) {
                $export_path = $app['I3Msg']['file'];
            }

            if ($input->getOption('dump')) {
                $script = $i55wm->dump($config_name, $I3Msg);
                $output->writeln($script);
            }
            else {
                $script = $i55wm->run($config_name, $I3Msg, $export_path);
            }
        }
        else {
            $out = '';
            foreach($i55wm->getConfigsNames() as $name) {
                $out .= " - $name \n";
            }
            $output->write("\n<info>The config « $config_name » doesn't exist please choose one of these :</info>\n $out \n\n");
        }
    })
    ;

$console
    ->register('assetic:dump')
    ->setDescription('Dumps all assets to the filesystem')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $dumper = $app['assetic.dumper'];
        if (isset($app['twig'])) {
            $dumper->addTwigAssets();
        }
        $dumper->dumpAssets();
        $output->writeln('<info>Dump finished</info>');
    })
;


return $console;
