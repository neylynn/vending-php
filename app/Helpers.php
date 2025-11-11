<?php
function view(string $file, array $vars=[]){
  extract($vars); include __DIR__."/../views/$file.php";
}


function flash($name, $message = '') {
    if ($message) {
        $_SESSION[$name] = $message;
    } elseif (isset($_SESSION[$name])) {
        $msg = $_SESSION[$name];
        unset($_SESSION[$name]);
        return $msg;
    }
    return null;
}