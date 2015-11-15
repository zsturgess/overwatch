<?php

foreach($container->getParameterBag()->all() as $key => $value) {
    $envValue = getenv( 'OVERWATCH_' . strtoupper($key) );

    if ($envValue !== false) {
        $container->setParameter($key, $envValue);
    }
}
