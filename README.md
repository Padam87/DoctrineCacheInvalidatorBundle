# DoctrineCacheInvalidatorBundle
A simplistic way to invalidate Doctrine result cache

Please take a look at the official Doctrine invalidation before using this bundle:
http://doctrine-orm.readthedocs.org/en/latest/reference/second-level-cache.html

If you need a complete second level cache, this is not the bundle you are looking for. However if you would like to cache some **custom query results**, you might find this helpful.

## Usage

At the moment only **annotation** configuration is supported.

```php
use Padam87\DoctrineCacheInvalidatorBundle\Annotation\InvalidateCache;

/**
 * @ORM\Entity()
 * @InvalidateCache("my_cache_key", events = {"PERSIST", "DELETE"})
 */
class Entity
{
    // ...
}
```
