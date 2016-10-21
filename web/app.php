<?php

use Symfony\Component\HttpFoundation\Request;

/*
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
Request::setTrustedProxies(array('127.0.0.1', $request->server->get('REMOTE_ADDR')));

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
