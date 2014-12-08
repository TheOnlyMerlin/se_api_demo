<?php 

class SeQuestions {

	public static function getQuestions($id, $queryArgs = [], $api = '2.2') {

		if (is_numeric($id)) {
			return SeQuestions::getQuestionsByID($id, $queryArgs, $api);
		}  else {
			return SeQuestions::getQuestionsByName($id, $queryArgs, $api);
		}

	}

	public static function getQuestionsByName($userName, $queryArgs = [], $api = '2.2') {

		// Get list of candidates
		$candidates = SeUsers::getPossibleUsersByName($userName, $queryArgs, $api);

		$result = SeQuestions::sendQuestionsQuery($candidates, $queryArgs, $api);

		return $result;
	}

	public static function getQuestionsByID($userID, $queryArgs = [], $api = '2.2') {

		$result = SeQuestions::sendQuestionsQuery([$userID], $queryArgs, $api);

		return $result;
	}

	/**
	 * 
	 * @param users 		
	 * @param queryArgs 	
	 *						sort - activity, votes, creation
	 * @param api 
	 * @return Array A Guzzle response object
	 */
	public static function sendQuestionsQuery($users, $args = [], $api = '2.2') {
		
		// Handle defaults
			// Default values for $args if not specified
		$defaults = [
			'order' => 'desc',
			'sort' => 'creation',
			'site' => 'stackoverflow'
		];

			// Replace values from the defaults with the ones supplied from $args
		$args = array_replace_recursive($defaults, $args);

			// Guzzle parameters
		$params = [
			'query' => $args,
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			]
		];

		// Validation
			// Must be an array, and must not be empty
		if (!is_array($users) || count($users) < 1 ) { 
			App::abort(400, "Invalid parameters, no users specified."); 
		};

		// Caching
			// Make a unique key to cache
		$cacheKey = SeQuestions::makeCachekey($users, $args, $api, 'sendQuestionsQuery');

			// Check the cache for the key
		if (Cache::has($cacheKey)) {
			// Found in cache, fetch it.
			$result = unserialize(Cache::get($cacheKey));
		} else {
			// Set the base URL
			$baseURL = 'https://api.stackexchange.com/' . $api . '/users/' . implode(';', $users) . '/questions';

			// Use Guzzle to get what we want
			$client = new GuzzleHttp\Client();
			
			// Send the GET request, and return the result
			$result = $client->get($baseURL, $params)->json();

			// Save result into cache for 60 mins
			Cache::add($cacheKey, serialize($result), 60);
		}

		// Check the result 
		return $result;
	}

	/**
	 * ** Cached **
	 * Gets information about a particular question by it's ID.  Looking up by
	 * user name is not supported via this method.
	 * @return Array Returns an array with information about the specified 
	 */
	public static function getQuestionById($id, $args = [], $api = '2.2') {

		// Handle defaults
			// Default values for $args if not specified
		$defaults = [
			'order' => 'desc',
			'sort' => 'creation',
			'site' => 'stackoverflow'
		];

			// Replace values from the defaults with the ones supplied from $args
		$args = array_replace_recursive($defaults, $args);

			// Guzzle parameters
		$params = [
			'query' => $args,
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			]
		];

		// Caching
			// Make a unique key to cache
		$cacheKey = SeQuestions::makeCachekey($id, $args, $api, 'getQuestionById');

			// Check the cache for the key
		if (Cache::has($cacheKey)) {
			// Found in cache, fetch it.
			$result = unserialize(Cache::get($cacheKey));
		} else {
			// No valid cache, fetch result

			// Implode requires an array
			if (!is_array($id)) { $id = [$id]; }

			// Prepare for api call
			$baseURL = 'https://api.stackexchange.com/' . $api . '/questions/' . implode(';', $id);

			// Make a new Guzzle client to use
			$client = new GuzzleHttp\Client();

			// Send the GET request, and return the result as an Array
			$result = $client->get($baseURL, $params)->json();

			// Actual info is the first item in the items array
			$result = $result['items'][0];

			// Save result into cache for 60 mins
			Cache::add($cacheKey, serialize($result), 60);

			// Make sure the "tags" value is always set
			if (!isset($result['tags'])) { $result['tags'] = []; }
		}

		return $result;
	}

	public static function makeCachekey($users, $queryArgs = [], $api = '2.2', $extra = "") {

		// Implode requires an array
		if (!is_array($users)) {$users = [$users]; }

		// Join together the specified users, the args, and the api to make a unique key
		return $extra . implode($users) . implode($queryArgs) . $api;
	}
}