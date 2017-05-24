<?php

namespace ChatApp\Controllers;

class Controller {

  protected $container;

  public function __construct($container) {
    $this->container = $container;
  }

  public function __get($property) {
    if ($this->container->{$property}) {
      return $this->container->{$property};
    }
  }

  protected static function isNullOrWhitespace($text) {
    if (!isset($text) || trim($text) === '') {
      return true;
    }
    return false;
  }
}
