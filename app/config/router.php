<?php

$router = $di->getRouter();

// Define your routes here
$router->add('/logout', [
    'controller' => 'login',
    'action' => 'logout'
]);
$router->handle();
