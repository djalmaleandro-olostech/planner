# ecidadao-api

## Introduction

This is a skeleton application using the Hyperf framework. This application is meant to be used as a starting place for those looking to get their feet wet with Hyperf Framework.

## Requirements

Hyperf has some requirements for the system environment, it can only run under Linux and Mac environment, but due to the development of Docker virtualization technology, Docker for Windows can also be used as the running environment under Windows.

The various versions of Dockerfile have been prepared for you in the [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) project, or directly based on the already built [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) Image to run.

## Installation
After this set **_app.build.target_** to **_dev_** on docker/docker-compose.yml
```
app:
    build:
        context: .
        target: dev
```

And run
```
docker-compose up --build
```


## Samples
See the project samples in this folder here.