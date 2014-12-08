<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
*/

Route::get('/', function()
{
	return View::make('index');
});

// Main api route
Route::group(['prefix' => 'api/v0.1'], function() {

	Route::resource('se/user', 'SeUserController', 
		['only' => 'show']);

	Route::resource('se/user.questions', 'SeQuestionController', 
		['only' => ['index']]);

	Route::resource('se/user.answers', 'SeAnswerController',
		['only' => ['index']]);
});