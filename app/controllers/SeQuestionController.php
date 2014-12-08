<?php

class SeQuestionController extends \BaseController {

	/**
	 * Display a listing of questions for a given user on StackExchange
	 *
	 * @param int $id The Stack Exchange User ID (user names are not unique on Stack Exchange)
	 * @return Response
	 */
	public function index($id)
	{
		// Use specified url parameters
		$args = [
			'page' => 1,
			'pagesize' => 10
		];

		// Return response array
		return SeQuestions::getQuestions($id, $args);
	}

}
