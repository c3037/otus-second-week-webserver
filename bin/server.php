#!/usr/bin/env php
<?php

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Server;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once dirname(__DIR__) . '/vendor/autoload.php';

set_time_limit(0);
ob_implicit_flush();

printf('Building DI container...%s', PHP_EOL);
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/app'));
$loader->load(dirname(__DIR__) . '/app/parameters.yaml');
$loader->load(dirname(__DIR__) . '/app/services.yaml');

printf('Initializing server...%s', PHP_EOL);
$server = new Server($container);

printf('Binding IPC signal handlers...%s', PHP_EOL);
pcntl_async_signals(true);
pcntl_signal(SIGINT, function () use ($server) {
    printf('%sInterrupting server...%s', PHP_EOL, PHP_EOL);
    $server->interruptHandler();
    exit;
});

printf('Starting server...%s', PHP_EOL);
$server->run();

printf('Server is ready to accept connections...%s', PHP_EOL);
while (true) {
    sleep(PHP_INT_MAX);
}
