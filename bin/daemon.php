#!/usr/bin/env php
<?php

$pid = pcntl_fork();

if (empty($pid)) {

    fclose(STDIN);
    fclose(STDOUT);
    fclose(STDERR);

    chdir(__DIR__);

    $sid = posix_setsid();

    $logger = function ($string) {
        $path = dirname(__DIR__) . '/var/daemon.log';
        file_put_contents($path, $string, FILE_APPEND);
    };

    ob_start($logger, 10);
    require_once __DIR__ . '/server.php';
    ob_flush();
    exit;
}

printf('Server has been run. Pid: %s%s', $pid, PHP_EOL);
