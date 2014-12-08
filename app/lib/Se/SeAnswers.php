<?php 

class SeAnswers {

	public static function getAnswers($id, $queryArgs = [], $api = '2.2') {

		if (is_numeric($id)) {
			return SeAnswers::getAnswersByID($id, $queryArgs, $api);
		}  else {
			return SeAnswers::getAnswersByName($id, $queryArgs, $api);
		}

	}

	/**
	 * ** Cached **
	 */
	public static function getAnswersByName($userName, $queryArgs = [], $api = '2.2') {

		// Get list of candidates
		$candidates = SeUsers::getPossibleUsersByName($userName, $queryArgs, $api);

			// Make a key to uniquely identify this query
		$cacheKey = SeAnswers::makeCachekey([$userName], $queryArgs, $api, "getAnswersByName");

		if (Cache::has($cacheKey)) {
			// Found in cache, fetch it.

			$result = unserialize(Cache::get($cacheKey));
		} else {
			// No valid cache, fetch result
			$result = SeAnswers::sendAnswersQuery($candidates, $queryArgs, $api)->json();

			// For each answer, add data about it's question
			foreach ($result['items'] as &$answer) {
				$question = SeQuestions::getQuestionById($answer['question_id']);

				// Map the title and tags from the question to the answer
				$answer['tags'] 
					= $question['tags'];
				$answer['title'] 
					= (isset($question['title']) ? $question['title']:'(No title)');
			}

			// Save result into cache for 60 mins
			Cache::add($cacheKey, serialize($result), 60);
		}

		return $result;
	}

	/**
	 * ** Cached **
	 */
	public static function getAnswersByID($userID, $queryArgs = [], $api = '2.2') {

		$result = SeAnswers::sendAnswersQuery([$userID], $queryArgs, $api)->json();

		// Uses caching
			// Make a key to uniquely identify this query
		$cacheKey = SeAnswers::makeCachekey($userID, $queryArgs, $api, "getAnswersByID");

			// Check cache to see if we should load the results stored there
		if (Cache::has($cacheKey)) {
			// Found in cache, fetch it.

			$result = unserialize(Cache::get($cacheKey));
		} else {
			// No valid cache, fetch result
			$result = SeAnswers::sendAnswersQuery([$userID], $queryArgs, $api)->json();

			// Save result into cache for 60 mins
			Cache::add($cacheKey, serialize($result), 60);
		}

		return $result;
	}

	/**
	 * 
	 * 
	 * @param users 		
	 * @param queryArgs 	
	 *						sort - activity, creation, votes
	 * @param api 
	 * @return Array A Guzzle response object
	 */
	public static function sendAnswersQuery($users, $queryArgs = [], $api = '2.2') {

		// Handle defaults
			// Default values for $args if not specified
		$defaults = [
			'order' => 'desc',
			'sort' => 'creation',
			'site' => 'stackoverflow'
		];

			// Replace values from the defaults with the ones supplied from $args
		$queryArgs = array_replace_recursive($defaults, $queryArgs);

			// Guzzle parameters
		$params = [
			'query' => $queryArgs,
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

		// Prepare for API call
			// Make a semicolon-delimited string from array
		$sUsers = implode(';', $users);

			// Set the base URL
		$baseURL = 'https://api.stackexchange.com/' . $api . '/users/' . $sUsers . '/answers';

			// use Guzzle to get what we want
		$client = new GuzzleHttp\Client();

		// Send the GET request, and set the parsed JSON as the result
		$result = $client->get($baseURL, $params);

		// Return the response 
		return $result;
	}

	public static function makeCachekey($users, $queryArgs = [], $api = '2.2', $extra = "") {

		// implode requires an array
		if (!is_array($users)) { $users = [$users]; }

		// Join together the specified users, the args, and the api to make a unique key
		return $extra . implode($users) . implode($queryArgs) . $api;
	}
}