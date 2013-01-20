<?php
namespace B55\I55WebManager\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;

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
                        $i55wm->getConfigs($config)->addWorkspace($i55wm->getConfigs($config)->createWorkspace($data['name']));
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


        })->value('workspace', 'new')
            ->bind('i55wm-workspace');

        return $controllers;
    }

}
