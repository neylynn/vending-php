<?php
class AuthOnly {
  public static function enforce(Auth $auth) {
    if(!$auth->check()) {
      header('Location: /login'); exit;
    }
  }
}
