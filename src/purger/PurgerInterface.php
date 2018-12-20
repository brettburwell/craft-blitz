<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\purger;

use craft\base\ComponentInterface;

interface PurgerInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    /**
     * Purges the cache given an array of cache IDs.
     *
     * @param int[] $cacheIds
     */
    public function purge(array $cacheIds);
}