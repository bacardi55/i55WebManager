<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use B55\I55WebManager\Controller\I55WebManagerControllerProvider as I55wmController;

$app->mount('/i55WebManager', new I55wmController());

$app->match('/', function () use ($app) {
    return $app->redirect($app['url_generator']->generate('i55WebManager'));
});

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;
