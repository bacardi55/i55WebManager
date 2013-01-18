<?php
namespace B55\I55WebManager\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;

use B55\I55WebManager as I55WebManager;
//use B55\I55WebManager\Forms\I55wmForms as I55wmForms;

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
                'configs' => $i55wm->getConfigsNames(), // TODO REMOVE
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


        return $controllers;
    }
}
