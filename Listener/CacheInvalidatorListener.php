<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Listener;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerAware;

class CacheInvalidatorListener extends ContainerAware
{
    /**
     * Invalidation rules
     *
     * @var array
     */
    private $rules = [];

    private $rulesLoaded = false;

    public function getRules()
    {
        if (!$this->rulesLoaded) {
            $ruleReader = $this->container->get('padam87_doctrine_cache_invalidator.rule_reader');
            $ruleReader->cacheRules();

            $this->rules       = include $ruleReader->getCacheFilename();
            $this->rulesLoaded = true;
        }

        return $this->rules;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em    = $args->getEntityManager();
        $uow   = $em->getUnitOfWork();
        $cache = $em->getConfiguration()->getResultCacheImpl();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->invalidate($cache, $entity, 'PERSIST');
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->invalidate($cache, $entity, 'UPDATE');
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->invalidate($cache, $entity, 'DELETE');
        }
    }

    public function invalidate(Cache $cache, $entity, $event)
    {
        $rules = $this->getRules();

        if (!isset($rules[get_class($entity)])) {
            $rules[get_class($entity)] = [];
        }

        foreach ($rules[get_class($entity)] as $rule) {
            if (in_array($event, $rule['events'])) {
                if ($cache->contains($rule['id'])) {
                    $cache->delete($rule['id']);
                }
            }
        }
    }
}
