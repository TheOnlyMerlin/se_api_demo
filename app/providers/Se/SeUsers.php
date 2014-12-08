<?php 

class SeUsers {
	
	/**
	 * Quesries the StackExchange API to get a list of possible users 
	 * (identified by user IDs) that it could be.  This is needed because User
	 * names are not neccessarily unique on Stack Exchange.
	 * @param name The user name to look 
	 * @param args Various arguments for the send
	 *		Structure:
	 *			query
	 *				api - what version of the stackexchange api to use
	 * 				page
	 *				pagesize
	 *				fromdate 
	 *				todate
	 *				order - desc / asc
	 *				min
	 *				max
	 *				sort - Options: reputation, creation, name, modified
	 *				site - What site is being searched. Default: StackOverflow
	 *			api - What version of the api is being used?
	 * @return Array An array of possible users, based on specified user name
	 */
	public static function getPossibleUsersByName($name, $args = []) {

		// Default values for $args if not specified
		$defaults = [
			'query' => [
				'inname' => $name
			]
		];

		// Replace values from the defaults with the ones supplied from $args
		$args = array_replace_recursive($defaults, $args);

		// Make empty results array by default
		$result = [];

		// Now get our list of possible users
		$queryResult = SeUsers::sendUsersQuery($args['query']);

		// parse the json from the result
		$data = $queryResult->json();

		// Check to see how many users we have.  if none, throw error
		if (count($data['items']) < 1) {
			App::abort(404, 'No users found!');
		}

		// generate results array by getting the id out of each record
		foreach ($data['items'] as $user) {
			$result[] = $user['user_id'];
		}

		return $result;
	}

	/**
	 * 
	 * 
	 * @param queryArgs Array A series of url args to include with the api call.
	 * @param api String Specifies what StackExchange API is being used
	 * @return reponse Returns a Guzzle response object.
	 */
	public static function sendUsersQuery($queryArgs = [], $api = '2.2') {

		// Default values for $args if not specified
		$defaults = [
			'order' => 'desc',
			'sort' => 'name',
			'site' => 'stackoverflow'
		];

		// Replace values from the defaults with the ones supplied from $args
		$queryArgs = array_replace_recursive($defaults, $queryArgs);


		// Set the base URL
		$baseURL = 'https://api.stackexchange.com/' . $api . '/users/';

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

		return $result;
	}
}