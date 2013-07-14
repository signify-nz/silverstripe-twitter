<?php

/**
 * Caches a wrapped twitter service
 * 
 * @author Damian Mooyman
 * 
 * @package twitter
 */
class CachedTwitterService implements ITwitterService {

	/**
	 * @var ITwitterService
	 */
	protected $cachedService = null;

	function __construct(ITwitterService $service) {
		$this->cachedService = $service;
	}

	function getTweets($user, $count) {
		// Init caching
		$cacheKey = "getTweets_{$user}_{$count}";
		$cache = SS_Cache::factory('CachedTwitterService');
		
		// Return cached value, if available
		if($rawResult = $cache->load($cacheKey)) return unserialize($rawResult);
		
		// Save and return
		$result = $this->cachedService->getTweets($user, $count);
		$cache->save(serialize($result), $cacheKey, array(), Config::inst()->get('CachedTwitterService', 'lifetime'));
		
		// Refresh the 'TimeAgo' field, as the cached value would now be outdated
		if($result) foreach($result as $index => $item) {
			$result[$index]['TimeAgo'] = TwitterService::determine_time_ago($item['Date']);
		}
		
		return $result;
	}

}
