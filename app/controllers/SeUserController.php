<?php

class SeUserController extends \BaseController {

	/**
	 * Display the specified resource.
	 *
	 * @param  String $id The name of the Stack Exchange user to display
	 * @return Response
	 */
	public function show($id)
	{
		$response = SeUsers::getPossibleUsersByName($id);

		return $response;
	}
}
