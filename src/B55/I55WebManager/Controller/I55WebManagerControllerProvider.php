<?php
namespace B55\I55WebManager\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

class I55WebManagerControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

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
                'configs' => $i55wm->getConfigsNames(),
                'has_configuration' => $i55wm->has_configuration(),
                'workspaces' => $app['I55wm']->getconfigsNames(),
            ));
        }
            return $app['twig']->render('i55wm.index.html.twig', array());
        })->bind('i55WebManager');


        return $controllers;
    }
}
