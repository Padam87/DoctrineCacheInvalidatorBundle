<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Tests\Listener;

use \Mockery as m;
use Padam87\DoctrineCacheInvalidatorBundle\Listener\CacheInvalidatorListener;

class CacheInvalidatorListenerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testOnFlush()
    {
        $entity = new \stdClass();
        $entity->relation = new \stdClass();
        $entity->relation->id = 100;
        $entity->property = 999;

        $cache = m::mock('Doctrine\Common\Cache\Cache');
        $cache->shouldReceive('contains')->with('some_cache_key_100_999')->once()->andReturn(true);
        $cache->shouldReceive('delete')->with('some_cache_key_100_999')->once();

        $config = m::mock('Doctrine\ORM\Configuration');
        $config->shouldReceive('getResultCacheImpl')->once()->andReturn($cache);

        $uow = m::mock('Doctrine\ORM\UnitOfWork');
        $uow->shouldReceive('getScheduledEntityInsertions')->once()->andReturn([$entity]);
        $uow->shouldReceive('getScheduledEntityUpdates')->once()->andReturn([]);
        $uow->shouldReceive('getScheduledEntityDeletions')->once()->andReturn([]);

        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('getUnitOfWork')->once()->andReturn($uow);
        $em->shouldReceive('getConfiguration')->once()->andReturn($config);

        $args = m::mock('Doctrine\ORM\Event\OnFlushEventArgs');
        $args->shouldReceive('getEntityManager')->once()->andReturn($em);

        $ruleReader = m::mock('Padam87\DoctrineCacheInvalidatorBundle\Rule\RuleReader');
        $ruleReader->shouldReceive('cacheRules')->once();
        $ruleReader
            ->shouldReceive('getCacheFilename')
            ->once()
            ->andReturn(__DIR__ . '/../Resources/InvalidationRules.cache.php')
        ;

        $container = m::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->shouldReceive('get')
            ->with('padam87_doctrine_cache_invalidator.rule_reader')
            ->once()
            ->andReturn($ruleReader)
        ;

        $listener = new CacheInvalidatorListener();
        $listener->setContainer($container);
        $listener->onFlush($args);
    }
}
