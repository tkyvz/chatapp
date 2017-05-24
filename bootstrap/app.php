<?php

use Respect\Validation\Validator as v;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
    'db' => [
      'driver' => 'sqlite',
      'database' => __DIR__ . '/../db/chatapp.db',
      'prefix' => '',
    ],
  ]
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {
  return $capsule;
};

$container['authenticator'] = function($container) {
  return new \ChatApp\Authentication\Authenticator;
};

$container['flash'] = function($container) {
  return new \Slim\Flash\Messages;
};

$container['view'] = function($container) {
  $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
    'cache' => false,
  ]);

  $view->addExtension(new \Slim\Views\TwigExtension(
    $container->router,
    $container->request->getUri()
  ));

  $view->getEnvironment()->addGlobal('authenticator', [
    'check' => $container->authenticator->check(),
    'user' => $container->authenticator->user(),
  ]);

  $view->getEnvironment()->addGlobal('flash', $container->flash);

  return $view;
};

$container['validator'] = function($container) {
  return new \ChatApp\Validation\Validator;
};

$container['HomeController'] = function($container) {
  return new \ChatApp\Controllers\HomeController($container);
};

$container['AuthController'] = function($container) {
  return new \ChatApp\Controllers\AuthController($container);
};

$container['UserController'] = function($container) {
  return new \ChatApp\Controllers\UserController($container);
};

$container['RoomController'] = function($container) {
  return new \ChatApp\Controllers\RoomController($container);
};

$container['RoomMemberController'] = function($container) {
  return new \ChatApp\Controllers\RoomMemberController($container);
};

$container['RoomAdminController'] = function($container) {
  return new \ChatApp\Controllers\RoomAdminController($container);
};

$container['csrf'] = function($container) {
  return new \Slim\Csrf\Guard;
};

$app->add(new \ChatApp\Middleware\ValidationMiddleware($container));
$app->add(new \ChatApp\Middleware\SignupMiddleware($container));
# $app->add(new \ChatApp\Middleware\CsrfViewMiddleware($container));

# $app->add($container->csrf);

v::with('ChatApp\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
