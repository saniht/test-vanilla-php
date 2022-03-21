<?php

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance.
|
*/

$builder = new \DI\ContainerBuilder();
$builder->useAutowiring(true);
$builder->useAnnotations(false);
$builder->ignorePhpDocErrors(true);
$builder->addDefinitions(__DIR__.'/../config/providerConfig.php');

return $builder->build();




