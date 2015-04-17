<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Listener;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
                $id = $this->getCacheId($rule, $entity);

                if ($cache->contains($id)) {
                    $cache->delete($id);
                }
            }
        }
    }

    /**
     * @param array  $rule
     * @param object $entity
     *
     * @return string
     */
    protected function getCacheId(array $rule, $entity)
    {
        $id = $rule['id'];

        $accessor = PropertyAccess::createPropertyAccessor();

        return preg_replace_callback(
            '/\$\{([^}]*)\}/',
            function($match) use ($accessor, $entity) {
                $value = $entity;

                foreach (explode('.', $match[1]) as $property)
                {
                    $value = $accessor->getValue($value, $property);
                }

                return $value;
            },
            $id
        );
    }
}
