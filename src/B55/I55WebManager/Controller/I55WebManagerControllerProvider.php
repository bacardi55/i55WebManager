<?php
namespace B55\I55WebManager\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use B55\I55WebManager as I55WebManager;

// TODO: Remove this
require_once __DIR__ . '/../Resources/lib/utils.php';

class I55WebManagerControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // INDEX:
        $controllers->match('/', function (Application $app) {
            $i55wm = $app['I55wm'];

            $template = 'i55wm.index.html.twig';

            if ($app['I55wm']->is_new() === true) {
                return $app['twig']->render($template,  array(
                    'configs' => null,
                    'has_configuration' => false,
                    'workspaces' => array(),
                ));
            }
            else {
                return $app['twig']->render($template,  array(
                    'configs' => $i55wm->getConfigs(),
                    'has_configuration' => $i55wm->has_configuration(), // TODO REMOVE
                    'configurations' => $app['I55wm']->getConfigsNames(),
                ));
            }
            return $app['twig']->render('i55wm.index.html.twig', array());
        })->bind('i55WebManager');


        // CONFIGURATION:
        // Configuration HP.
        $controllers->match('/configuration', function (Application $app) {
            $defaultWorkspaces = NULL;
            $i55wm = $app['I55wm'];
            $i55wm->load();
            $defaultWorkspaces = $i55wm->getConfiguration()->getDefaultWorkspaces();

            return $app['twig']->render('i55wm.configuration/i55wm.index.html.twig', array(
              'defaultWorkspaces' => $defaultWorkspaces,
              'configurations' => $app['I55wm']->getConfigsNames(),
            ));
        })->bind('i55wm-configuration');

        // Configuration parse file.
        $controllers->match('/configuration/load', function (Request $request, Application $app) {
            //TODO: add load file from files
            $form_view = $i55ParsedConfig = $upload = $config_file = false;

            $i55wm = $app['I55wm'];
            $i55Form = new I55WebManager\Forms\I55wmForms($app['form.factory']);
            $form = $i55Form->getUploadConfigForm();

            if ('POST' === $request->getMethod() || is_file($app['i55_config_file'])) {
                if ($request->getMethod() === 'POST') {
                    $form->bind($request);
                    if ($form->isValid()) {
                        $dir = __DIR__ . '/Resources';
                        $data = $form->getData();
                        $file = $form['config_file']->getData();
                        $ret = $file->move($dir, $file->getClientOriginalName());
                        $filename = $file->getClientOriginalName();
                        $file = $dir . '/' . $filename;
                    }
                }
                else {
                    $file = $app['i55_config_file'];
                }

                $i55ConfigParser = new I55WebManager\I55ConfigParser($file);
                if (count($i55ParsedConfig)) {
                    $app['session']->setFlash('success', 'Your i3 config file has been parsed successfully');
                } else {
                    $app['session']->setFlash('error', 'Error while parsing your i3 config file');
                }

                $i55ParsedConfig = $i55ConfigParser->parse();
                $i55wm->getConfiguration()->setDefaultWorkspaces($i55ParsedConfig);

                $i55wm->save();

                return $app->redirect(
                  $app['url_generator']->generate('i55wm-configuration')
                );
            }
            else {
                $upload = true;
            }

            return $app['twig']->render('i55wm.configuration/i55wm.load.html.twig', array(
                'upload_form' => $upload,
                'config_file' => $config_file,
                'form' => $form->createView(),
                'config' => $i55ParsedConfig,
                'configurations' => $app['I55wm']->getConfigsNames(), //TODO try to optimize this
            ));
        })->bind('i55wm-load-config');


        // I3CONFIG:
        // config.
        $controllers->match('/i3config/{config_name}',
            function (Request $request, $config_name) use ($app) {

            $i55wm = $app['I55wm'];
            $i55Form = new I55WebManager\Forms\I55wmForms($app['form.factory']);
            $data = array();

            if ($config_name != 'new') {
                $i55Config = $i55wm->getConfigs($config_name);
                $data['config_name'] = $i55Config->getName();
                $data['config_nb_workspace'] = count($i55Config->getWorkspaces());
                $data['exists'] = true;
            }
            else {
                $data['exists'] = false;
                $i55Config = $i55wm->createConfig();
            }

            $form = $i55Form->getAddForm($data);

            if ('POST' === $request->getMethod()) {
                $form->bind($request);

                if ($form->isValid()) {
                    $data = $form->getData();

                    $i55Config->setName($data['config_name']);
                    if (!array_key_exists('exists', $data) || $data['exists'] == false) {
                        if (array_key_exists('use_default_workspace', $data)
                              && count($data['use_default_workspace'])
                              && in_array('y', $data['use_default_workspace'])) {

                            if (!$i55wm->getConfiguration()->hasDefaultWorkspaces()) {
                                $app['session']->setFlash(
                                    'error',
                                    'You don\'t have set your default workspaces. An empty config has been created instead'
                                );
                            }
                            else {
                                $app['session']->setFlash(
                                    'success',
                                    'Your default workspaces have been loaded'
                                );
                                $i55Config->setWorkspaces($i55wm->getConfiguration()->getDefaultWorkspaces());
                            }
                        }
                        $i55wm->addConfig($i55Config);

                        $app['session']->setFlash(
                            'success',
                            'Your config has been created'
                        );
                    }
                    else {
                        if ($i55wm->save()) {
                            $app['session']->setFlash(
                                'success',
                                'Your config has been updated'
                            );
                        }
                        else {
                            $app['session']->setFlash(
                                'error',
                                'Your config has not been updated'
                            );
                        }
                    }

                    return $app->redirect(
                        $app['url_generator']->generate(
                            'i55wm-i3config', array('config_name' => $data['config_name'])
                        )
                    );
                }
            }

            return $app['twig']->render('i55wm.i3config/i55wm.config.html.twig', array(
                'configurations' => $app['I55wm']->getConfigsNames(), //TODO try to optimize this
                'form' => $form->createView(),
                'exists' => $data['exists'],
                'config' => $i55Config,
            ));

        })->value('config_name', 'new')
            ->bind('i55wm-i3config');

        $controllers->match('/i3config/{config}/remove',
            function (Application $app, $config) {

            $i55wm = $app['I55wm'];
            $i55Config = $i55wm->getConfigs($config);

            if (is_object($i55Config)) {
                $i55wm->removeConfig($config);
                $app['session']->setflash(
                    'success',
                    'your configuration «' . $config . '» has been deleted'
                );
            }
            else {
                $app['session']->setflash(
                    'error',
                    'your configuration «' . $config . '» doesn\'t exist'
                );
            }

            return $app->redirect(
                $app['url_generator']->generate('i55WebManager')
            );
        })->bind('i55wm-i3config-delete');

        // Workspace.
        $app->match('/i3config/{config}/workspace/{workspace_name}',
            function (Request $request, Application $app, $config, $workspace_name) {

            $i55wm = $app['I55wm'];

            $data = array();
            if ($workspace_name == 'new') {
                $i55Workspace = $i55wm->getConfigs($config)->createWorkspace();
                $data['exists'] = false;
            }
            else {
                $i55Workspace = $i55wm->getConfigs($config)->getWorkspaces($workspace_name);

                if (is_object($i55Workspace)) {
                    $data = array(
                        'name' => $i55Workspace->getName(),
                        'default_layout' => $i55Workspace->getDefaultLayout(),
                        'exists' => true,
                    );
                }
            }

            $i55Form = new I55WebManager\Forms\I55wmForms($app['form.factory']);
            $form = $i55Form->getWorkspaceForm($data, geti55Layouts());

            if ('POST' === $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    if ($data['exists'] == false) {
                        $i55Workspace = $i55wm->getConfigs($config)->createWorkspace($data['name']);
                        $i55Workspace->setDefaultLayout($data['default_layout']);
                        $i55wm->getConfigs($config)->addWorkspace($i55Workspace);
                        $app['session']->setFlash(
                          'success',
                          'Workspace «' . $data['name'] . '» created'
                        );
                    }
                    else {
                        $i55Workspace->setName($data['name']);
                        $i55Workspace->setDefaultLayout($data['default_layout']);
                        $app['session']->setflash(
                            'success',
                            'Workspace «' . $data['name'] .'» modified'
                        );
                    }
                    $i55wm->save();
                    return $app->redirect(
                        $app['url_generator']->generate(
                            'i55wm-i3config', array('config_name' => $i55wm->getConfigs($config)->getName())
                        )
                    );
                }
            }

            return $app['twig']->render('i55wm.i3config/i55wm.workspace.html.twig', array(
                'configurations' => $app['I55wm']->getConfigsNames(), //TODO try to optimize this
                'form' => $form->createView(),
                'config' => $i55wm->getConfigs($config),
                'workspace' => $i55Workspace,
            ));


        })->value('workspace_name', 'new')
            ->bind('i55wm-workspace');

        $app->match('/i3config/{config}/workspace/{workspace_name}/remove',
            function (Application $app, $config, $workspace_name) {

            $i55wm = $app['I55wm'];
            $i55Workspace = $i55wm->getConfigs($config)->getWorkspaces($workspace_name);

            if (is_object($i55Workspace)) {
                $i55wm->getConfigs($config)->removeWorkspace($workspace_name);
                $i55wm->save();
                $app['session']->setflash(
                    'success',
                    'your workspace«' . $workspace_name . '» has been deleted'
                );
            }
            else {
                $app['session']->setflash(
                    'error',
                    'your workspace «' . $workspace_name . '» doesn\'t exist'
                );
            }

            return $app->redirect(
                $app['url_generator']->generate(
                  'i55wm-i3config',
                  array('config_name' => $config)
                )
            );
        })->bind('i55wm-workspace-delete');


        $app->match('/i3config/{config}/workspace/{workspace}/client/{client}',
            function (Request $request, Application $app, $config, $workspace, $client) {

            $i55wm = $app['I55wm'];
            $data = array();
            if ($client == 'new') {
              $i55Client = $i55wm->getConfigs($config)
                  ->getWorkspaces($workspace)->createClient();
                $data['exists'] = false;
            }
            else {
                $i55Client = $i55wm->getConfigs($config)
                    ->getWorkspaces($workspace)->getClient($client);

                if (is_object($i55Client)) {
                    $data = array(
                        'name' => $i55Client->getName(),
                        'command' => $i55Client->getCommand(),
                        'arguments' => $i55Client->getArguments(),
                        'exists' => true,
                    );
                }
            }

            $i55Form = new I55WebManager\Forms\I55wmForms($app['form.factory']);
            $form = $i55Form->getClientForm($data);

            if ('POST' === $request->getMethod()) {
                $form->bind($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $i55Client->setName($data['name']);
                    $i55Client->setCommand($data['command']);
                    $i55Client->setArguments($data['arguments']);
                    if ($data['exists'] == false) {
                        $i55wm->getConfigs($config)->getWorkspaces($workspace)
                            ->addClient($i55Client);
                    }
                    $i55wm->save();

                    $app['session']->setFlash(
                      'success',
                      'Client «' . $data['name'] . '» added to your workspace «'
                          . $workspace .'»'
                    );

                    return $app->redirect(
                        $app['url_generator']->generate(
                          'i55wm-workspace',
                          array('config' => $config, 'workspace_name' => $workspace)
                        )
                    );
                }
            }

            return $app['twig']->render('i55wm.i3config/i55wm.client.html.twig', array(
                'configurations' => $app['I55wm']->getConfigsNames(), //TODO try to optimize this
                'form' => $form->createView(),
                'config' => $i55wm->getConfigs($config),
                'workspace' => $i55wm->getConfigs($config)->getWorkspaces($workspace),
                'client' => $i55Client
            ));
        })->bind('i55wm-client')->value('client', 'new');

        $app->match('/i3config/{config}/workspace/{workspace}/client/{client}/remove',
            function (Application $app, $config, $workspace, $client) {

            $i55wm = $app['I55wm'];
            $i55Client = $i55wm->getConfigs($config)
                ->getWorkspaces($workspace)
                ->getClient($client);

            if (is_object($i55Client)) {
                $i55wm->getConfigs($config)->getWorkspaces($workspace)
                    ->removeClient($client);
                $i55wm->save();
                $app['session']->setflash(
                    'success',
                    'your client «' . $client . '» has been removed'
                );
            }
            else {
                $app['session']->setflash(
                    'error',
                    'your client «' . $client . '» doesn\'t exist'
                );
            }
            return $app->redirect($app['url_generator']->generate(
                'i55wm-workspace',
                array('config' =>  $config, 'workspace_name' => $workspace)
            ));
        })->bind('i55wm-client-remove');


        $app->match('/download', function (Request $request, Application $app) {
            $link = '';

            if ('GET' === $request->getMethod() && $request->query->get('download') == 1) {
                $i55wm = $app['I55wm'];
                $yaml = $i55wm->generateYaml();

                return new Response(
                    $yaml,
                    200,
                    array('Content-Type' => 'text/x-yaml',
                    'Content-Disposition' => 'attachment; filename="i3Config.yaml"'
                    )
                );
            }

            return $app['twig']->render('i55wm.download.html.twig', array(
                'configurations' => $app['I55wm']->getConfigsNames(), //TODO try to optimize this
                'link' => $link
            ));
        })->bind('i55wm-download');


        return $controllers;
    }
}
