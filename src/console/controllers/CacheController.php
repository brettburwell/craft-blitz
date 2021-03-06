<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\console\controllers;

use Craft;
use craft\helpers\Console;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use putyourlightson\blitz\Blitz;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Performs functions on the Blitz cache.
 */
class CacheController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Clears the cache.
     *
     * @return int
     */
    public function actionClear(): int
    {
        return $this->_clearFlushCache(false);
    }

    /**
     * Flushes the cache.
     *
     * @return int
     */
    public function actionFlush(): int
    {
        return $this->_clearFlushCache(true);
    }

    /**
     * Clears and warms the entire cache.
     *
     * @return int
     */
    public function actionWarm(): int
    {
        $settings = Blitz::$plugin->getSettings();

        if (!$settings->cachingEnabled) {
            $this->stderr(Craft::t('blitz', 'Blitz caching is disabled.').PHP_EOL, Console::FG_RED);

            return ExitCode::OK;
        }

        if (empty($settings->cacheFolderPath)) {
            $this->stderr(Craft::t('blitz', 'Blitz cache folder path is not set.').PHP_EOL, Console::FG_RED);

            return ExitCode::OK;
        }

        $this->stdout(Craft::t('blitz', 'Flushing Blitz cache.').PHP_EOL, Console::FG_GREEN);

        Blitz::$plugin->cache->emptyCache(true);

        // Get warm cache URLS
        $urls = Blitz::$plugin->cache->getAllCacheableUrls();

        $total = count($urls);
        $count = 0;
        $success = 0;
        $urlErrors = false;
        $client = Craft::createGuzzleClient();
        $requests = [];

        $this->stdout(Craft::t('blitz', 'Warming Blitz cache.').PHP_EOL, Console::FG_GREEN);

        Console::startProgress(0, $total, '', 0.8);

        foreach ($urls as $url) {
            // Ensure URL is an absolute URL starting with http
            if (strpos($url, 'http') === 0) {
                $requests[] = new Request('GET', $url);
            }
            else {
                $urlErrors = true;
                $count++;
                Console::updateProgress($count, $total);
            }
        }

        // Create a pool of requests for sending multiple concurrent requests
        $pool = new Pool($client, $requests, [
            'concurrency' => $settings->concurrency,
            'fulfilled' => function () use (&$success, &$count, $total) {
                $success++;
                $count++;
                Console::updateProgress($count, $total);
            },
            'rejected' => function () use (&$count, $total) {
                $count++;
                Console::updateProgress($count, $total);
            },
        ]);

        // Initiate the transfers and wait for the pool of requests to complete
        $pool->promise()->wait();

        Blitz::$plugin->cache->cleanElementQueryTable();

        Console::updateProgress($total, $total);
        Console::endProgress();

        if ($urlErrors) {
            $this->stdout(Craft::t('blitz', 'One or more URLs do not begin with "http" and were ignored. Please ensure that your site’s base URLs do not use the @web alias.').PHP_EOL, Console::FG_RED);
        }

        $this->stdout(Craft::t('blitz', 'Blitz cache successfully warmed {success} files.', ['success' => $success]).PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }

    // Private Methods
    // =========================================================================

    /**
     * Clears or flushes the cache.
     *
     * @param bool $flush
     *
     * @return int
     */
    public function _clearFlushCache(bool $flush = false): int
    {
        $settings = Blitz::$plugin->getSettings();

        if (empty($settings->cacheFolderPath)) {
            $this->stderr(Craft::t('blitz', 'Blitz cache folder path is not set.').PHP_EOL, Console::FG_RED);

            return ExitCode::OK;
        }

        $this->stdout(Craft::t('blitz', '{action} Blitz cache.', [
            'action' => $flush ? 'Flushing' : 'Clearing'
        ]).PHP_EOL, Console::FG_GREEN);

        Blitz::$plugin->cache->emptyCache($flush);

        $this->stdout(Craft::t('blitz', 'Blitz cache successfully {action}.', [
            'action' => $flush ? 'flushed' : 'cleared'
        ]).PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}