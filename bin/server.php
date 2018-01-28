#!/usr/bin/env php
<?php

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Server;
use c3037\Otus\SecondWeek\BracketsServer\SignalBinder\Service\SignalBinderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

require_once dirname(__DIR__) . '/vendor/autoload.php';

set_time_limit(0);
ob_implicit_flush();

const PARAMETERS = 'parameters.yaml';
const SERVICES = 'services.yaml';

printf('Building DI container...%s', PHP_EOL);
$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/app'));
$loader->load(PARAMETERS);
$loader->load(SERVICES);

printf('Initializing server...%s', PHP_EOL);
$server = new Server($container);

printf('Binding IPC signal handlers...%s', PHP_EOL);
$signalBinder = $container->get('signal_binder');
/** @var SignalBinderInterface $signalBinder */
$signalBinder->setAsyncMode();
$signalBinder->bind(SIGHUP, function () use ($server, $loader) {
    printf('Reloading parameters...%s', PHP_EOL);
    $loader->load(PARAMETERS);
    $server->reload();
});
$signalBinder->bind(SIGTERM, function () use ($server) {
    printf('Terminating server...%s', PHP_EOL);
    $server->terminate();
    exit;
});

printf('Starting server...%s', PHP_EOL);
$server->run();
