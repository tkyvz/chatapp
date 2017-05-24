<?php

namespace ChatApp\Middleware;

class Middleware {

  protected $container;

  public function __construct($container) {
    $this->container = $container;
  }

  protected static function isNullOrWhitespace($text) {
    if (!isset($text) || trim($text) === '') {
      return true;
    }
    return false;
  }
}
