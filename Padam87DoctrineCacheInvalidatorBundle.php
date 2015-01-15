<?php

namespace Padam87\DoctrineCacheInvalidatorBundle;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Padam87DoctrineCacheInvalidatorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        AnnotationRegistry::registerAutoloadNamespace('\Padam87\DoctrineCacheInvalidatorBundle\Annotation');
    }
}
