# Setup
$ composer install

# Run

## Daemon
$ export BRACKETS_SERVER_HOST='localhost'

$ export BRACKETS_SERVER_PORT=19118

$ ./bin/daemon.php

## Docker
$ export BRACKETS_SERVER_PORT=19118

$ docker-compose up --build -d

# Usage
$ telnet localhost 19118

# Reload configs

## Daemon
$ kill -SIGHUP {daemonPid}

## Docker
$ docker kill --signal=HUP {containerId}
