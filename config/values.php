<?php

return [
    'workerman_socket_port' => env('DOCKER_WORKERMAN_SOCKET_PORT', "23450"),
    'workerman_register_port' => env('DOCKER_WORKERMAN_REGISTER_PORT', "12340"),
];
