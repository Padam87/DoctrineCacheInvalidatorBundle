<?php

namespace Padam87\DoctrineCacheInvalidatorBundle\Rule;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class RuleReader
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $annotationReader;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @param Reader                 $annotationReader
     * @param EntityManagerInterface $em
     * @param                        $cacheDir
     */
    public function __construct(Reader $annotationReader, EntityManagerInterface $em, $cacheDir)
    {
        $this->annotationReader = $annotationReader;
        $this->em               = $em;
        $this->cacheDir         = $cacheDir;
    }

    /**
     * Returns all invalidation rules for every entity
     *
     * @return array
     */
    public function getRules()
    {
        $rules = [];

        /** @var ClassMetadata $m */
        foreach ($this->getMetadata() as $m) {
            $rules[$m->getName()] = [];

            $annotations = $this->annotationReader->getClassAnnotations($m->getReflectionClass());

            foreach ($annotations as $annotation) {
                if ($annotation instanceof RuleInterface) {
                    $rules[$m->getName()][] = $annotation->toArray();
                }
            }
        }

        return $rules;
    }

    public function cacheRules($force = false)
    {
        if (!is_dir($this->cacheDir . '/padam87/doctrine_cache_invalidator')) {
            @mkdir($this->cacheDir . '/padam87/doctrine_cache_invalidator', 0777, true);
        }

        $filename = $this->getCacheFilename();

        $cache = new ConfigCache($filename, true);

        if (!$cache->isFresh() || $force) {
            $content  = '<?php return ' . var_export($this->getRules(), true) . ';';
            $cache->write($content, $this->getResources());
        }
    }

    public function getCacheFilename()
    {
        return $this->cacheDir . '/padam87/doctrine_cache_invalidator/InvalidationRules.cache.php';
    }

    protected function getResources()
    {
        $res = [];

        /** @var ClassMetadata $m */
        foreach ($this->getMetadata() as $m) {
            $res[] = new FileResource($m->getReflectionClass()->getFileName());
        }

        return $res;
    }

    protected function getMetadata()
    {
        return $this->em->getMetadataFactory()->getAllMetadata();
    }
}
