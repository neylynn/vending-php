<?php
return [
  'db' => [
    'host' => '127.0.0.1',   
    'port' => 3307,
    'name' => 'vending',  
    'user' => 'root',        
    'pass' => '',   
  ],

  'jwt' => [
    'issuer'          => 'vending-api',
    'secret'          => 'change_this_super_secret_key',
    'expiry_seconds'  => 14 * 24 * 60 * 60,
    'debug'           => false,
  ],
];
