<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Rule;

interface RuleInterface
{
    /**
     * @return string
     */
    public function getCacheId();

    /**
     * @return array
     */
    public function getEvents();

    /**
     * @return array
     */
    public function toArray();
}
