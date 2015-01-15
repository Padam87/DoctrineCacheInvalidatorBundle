<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Annotation;

use Padam87\DoctrineCacheInvalidatorBundle\Rule\RuleInterface;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class InvalidateCache implements RuleInterface
{
    /**
     * Cache id
     *
     * @var string
     */
    public $id;

    /**
     * Events to trigger the invalidation
     *
     * @var array
     */
    public $events = ['PERSIST', 'UPDATE', 'DELETE'];

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'     => $this->id,
            'events' => $this->events
        ];
    }
}
