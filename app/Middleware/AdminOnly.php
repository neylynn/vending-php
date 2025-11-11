<?php
class AdminOnly {
  public static function enforce(Auth $auth) {
    if(!$auth->check() || !$auth->isAdmin()) {
      http_response_code(403);
      echo "Forbidden: Admins only."; exit;
    }
  }
}
