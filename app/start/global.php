<?php

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
*/

ClassLoader::addDirectories(array(

	app_path().'/commands',
	app_path().'/controllers',
	app_path().'/models',
	app_path().'/database/seeds',

));

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a basic log file setup which creates a single file for logs.
|
*/

Log::useFiles(storage_path().'/logs/laravel.log');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

App::error(function(Exception $exception, $code) {

	Log::error($exception);

	// Handle all API errors as an API call.
	if (Request::is('api/*')) {

	// Handle API errors
	$message = $exception->getMessage();

	// Handle special cases from Guzzle
		// if possible forward Guzzle's HTTP codes over to the API user
	if ($exception instanceof GuzzleHttp\Exception\RequestException) {
		
		// Error is a networking error
		if ($exception instanceof GuzzleHttp\Exception\ClientException) {
			$code = $exception->getResponse()->getStatusCode();
		} else {
			$code = 500;
		}

		// Return the JSON api response
		return Response::json([
                    'code'      =>  $code,
                    'message'   =>  $exception->getMessage()
                ], $code); 
	}


	// switch statements provided in case you need to add
    // additional logic for specific error code.
    switch ($code) {
        case 401:
            return Response::json([
                    'code'      =>  401,
                    'message'   =>  $message
                ], 401);
        case 404:
            $message            = (!$message ? $message = 'the requested resource was not found' : $message);
            return Response::json([
                    'code'      =>  404,
                    'message'   =>  $message
                ], 404);        
    }

    // Fallback code for handling items not otherwise covered by the switch
    return Response::json([
    		'code' => $code,
    		'message' => $message
    	], $code);
	}

});



/*
|--------------------------------------------------------------------------
| Maintenance Mode Handler
|--------------------------------------------------------------------------
|
| The "down" Artisan command gives you the ability to put an application
| into maintenance mode. Here, you will define what is displayed back
| to the user if maintenance mode is in effect for the application.
|
*/

App::down(function()
{
	return Response::make("Be right back!", 503);
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require app_path().'/filters.php';
