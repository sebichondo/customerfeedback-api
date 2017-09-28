<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

$app->get('/', function ($request, $response, $args) {
    // Try to connect to the database and retrieve customers
    // If not found, create some customers
    // Return below if everything is ok.
    //return $response->withStatus(200)->write('Customer Legacy API is working. Here are the customers in the database.');

    // Otherwise
    return $response->withStatus(500)->write('Unable to connect to MySQL database.');    
});

$app->get('/customer/{email}', function (Request $request, Response $response) {
    $email = $request->getAttribute('email');
    $response->getBody()->write("Hello, $email");

    return $response;
});
$app->run();
