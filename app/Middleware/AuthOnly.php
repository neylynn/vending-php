<?php
class AuthOnly {
  public static function enforce(Auth $auth) {
    if(!$auth->check()) {
      // header('Location: /login'); exit;
      function redirect(string $url) {
          header("Location: $url");
          if (!defined('PHPUNIT_RUNNING')) {
              exit;
          }
      }

    }
  }
}
