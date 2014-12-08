<?php 

class SeAnswers {

	public static function getAnswers($id, $queryArgs = [], $api = '2.2') {

		if (is_numeric($id)) {
			return SeAnswers::getAnswersByID($id, $queryArgs, $api);
		}  else {
			return SeAnswers::getAnswersByName($id, $queryArgs, $api);
		}

	}

	public static function getAnswersByName($userName, $queryArgs = [], $api = '2.2') {

		// Get list of candidates
		$candidates = SeUsers::getPossibleUsersByName($userName, $queryArgs, $api);

		$result = SeAnswers::sendAnswersQuery($candidates, $queryArgs, $api)->json();

		return $result;
	}

	public static function getAnswersByID($userID, $queryArgs = [], $api = '2.2') {

		$result = SeAnswers::sendAnswersQuery([$userID], $queryArgs, $api)->json();

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
		// Default values for $args if not specified
		$defaults = [
			'order' => 'desc',
			'sort' => 'creation',
			'site' => 'stackoverflow'
		];

		// Validation
			// Must be an array, and must not be empty
		if (!is_array($users) || count($users) < 1 ) { 
			App::abort(400, "Invalid parameters, no users specified."); 
		};

		// Replace values from the defaults with the ones supplied from $args
		$queryArgs = array_replace_recursive($defaults, $queryArgs);

		// Make a semicolon-delimited string from array
		$sUsers = implode(';', $users);

		// Set the base URL
		$baseURL = 'https://api.stackexchange.com/' . $api . '/users/' . $sUsers . '/answers';

		// use Guzzle to get what we want
		$client = new GuzzleHttp\Client();

		$params = [
			'query' => $queryArgs,
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			]
		];

		// Send the GET request, and set the parsed JSON as the result
		$result = $client->get($baseURL, $params);

		// Check the result 
		return $result;
	}
}