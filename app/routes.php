<?php

use ChatApp\Middleware\AuthMiddleware;
use ChatApp\Middleware\CsrfViewMiddleware;
use ChatApp\Middleware\GuestMiddleware;
use ChatApp\Middleware\RestMiddleware;
use ChatApp\Middleware\RoomMemberMiddleware;
use ChatApp\Middleware\RoomAdminMiddleware;

$app->group('', function() use ($app, $container) {
  $app->get('/', 'HomeController:index')->setName('home');

  $app->group('', function() use ($app) {
    $app->get('/signup', 'AuthController:getSignUp')->setName('auth.signup');
    $app->post('/signup', 'AuthController:postSignUp');

    $app->get('/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $app->post('/signin', 'AuthController:postSignIn');
  })->add(new GuestMiddleware($container));

  $app->group('', function() use ($app) {
    $app->get('/signout', 'AuthController:getSignOut')->setName('auth.signout');
  })->add(new AuthMiddleware($container));
})->add(new CsrfViewMiddleware($container))->add($container->csrf);

$app->group('/rest', function() use ($app, $container) {
  $app->get('/search', 'UserController:search');

  $app->group('/room', function() use ($app, $container) {
    $app->group('/admin', function() use ($app) {
      $app->post('/member/new', 'RoomAdminController:addMember');
      $app->post('/member/kick', 'RoomAdminController:kickMember');
      $app->post('/new', 'RoomAdminController:makeAdmin');
      $app->post('/kick', 'RoomAdminController:fireAdmin');
    })->add(new RoomAdminMiddleware($container));

    $app->group('/member', function() use ($app) {
      $app->post('/leave', 'RoomMemberController:leaveRoom');
      $app->post('/message', 'RoomMemberController:sendMessage');
      $app->get('/message', 'RoomMemberController:getMessages');
      $app->get('/message/refresh', 'RoomMemberController:refresh');
      $app->get('/message/scroll', 'RoomMemberController:scroll');
      $app->get('/', 'RoomMemberController:getMembers');
    })->add(new RoomMemberMiddleware($container));

    $app->post('/', 'RoomController:addRoom');
    $app->get('/', 'RoomController:getRooms');
  });
})->add(new RestMiddleware($container));
