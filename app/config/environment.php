<?php

foreach($container->getParameterBag()->all() as $key => $value) {
  $env_value = getenv( 'OVERWATCH_' . strtoupper($key) );

  if($env_value !== false) {
    $container->setParameter($key, $env_value);
  }
}
