<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\CacheWarmer;

use Padam87\DoctrineCacheInvalidatorBundle\Rule\RuleReader;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class InvalidationRuleCacheWarmer extends CacheWarmer implements CacheWarmerInterface
{
    /**
     * @var RuleReader
     */
    protected $ruleReader;

    public function __construct(RuleReader $ruleReader)
    {
        $this->ruleReader = $ruleReader;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->ruleReader->cacheRules(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }
}
