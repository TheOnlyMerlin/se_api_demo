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

// Question routing, only show
//Route::resource('Question', 'QuestionController', 
//	array('only' => array('show')));

// Answer routing, only show
//Route::resource('Answer', 'AnswerController',
//	array('only' => array('show')));

//Route::get('question/{user}', array('as' => 'question', function() {
//	return View::make('question', array('user' => 'test'));
//}));

//Route::get('question/{user}', ['as' => 'question', 'uses' => 'SeController@dispQuestions']);

//Route::get('answer/{user}', array('as' => 'answer', function() {
//	return View::make('answer');
//}));

//Route::get('about', array('as' => 'about', function() {
//	return View::make('about');
//}));

// Main api route
Route::group(['prefix' => 'api/v0.1'], function() {

	Route::resource('se/user', 'SeUserController', 
		['only' => 'show']);

	Route::resource('se/user.questions', 'SeQuestionController', 
		['only' => ['index']]);

	Route::resource('se/user.answers', 'SeAnswerController',
		['only' => ['index']]);
});