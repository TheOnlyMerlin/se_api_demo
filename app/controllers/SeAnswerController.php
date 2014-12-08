<?php

class SeAnswerController extends \BaseController {

	/**
	 * Display a listing of answers for a given user on StackExchange
	 *
	 * @param String $id The Stack Exchange User name
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
		return SeAnswers::getAnswers($id, $args);
	}

}
