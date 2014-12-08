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

		$result = SeQuestions::sendQuestionsQuery($candidates, $queryArgs, $api)->json();

		return $result;
	}

	public static function getQuestionsByID($userID, $queryArgs = [], $api = '2.2') {

		$result = SeQuestions::sendQuestionsQuery([$userID], $queryArgs, $api)->json();

		return $result;
	}

	/**
	 * 
	 * 
	 * @param users 		
	 * @param queryArgs 	
	 *						sort - activity, votes, creation
	 * @param api 
	 * @return Array A Guzzle response object
	 */
	public static function sendQuestionsQuery($users, $queryArgs = [], $api = '2.2') {
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
		$baseURL = 'https://api.stackexchange.com/' . $api . '/users/' . $sUsers . '/questions';

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