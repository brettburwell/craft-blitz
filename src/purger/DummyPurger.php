<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\purger;

use putyourlightson\blitz\Blitz;

class DummyPurger implements PurgerInterface
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Dummy Purger';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function purge(array $cacheIds)
    {
        $urls = Blitz::$plugin->cache->getCacheUrls($cacheIds);
    }
}