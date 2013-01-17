<?php
namespace B55\I55WebManager\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

use B55\I55WebManager\I55WebManager as I55WebManager;

require_once __DIR__ . '/../Resources/lib/utils.php';

class I55wmServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['I55wm'] = $app->share(function ($app) {
          $default_file = getYamlFilePathFromApp($app);
          return new I55WebManager($default_file);
        });
    }

    public function boot(Application $app)
    {
    }
}
