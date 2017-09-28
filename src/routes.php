<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/init', function (Request $request, Response $response, array $args) {
    // Create table if it doesn't exists
    $this->logger->info("Creating table if it doesn't exist.");
    $createTableSQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `customers` (
        `id` int(11) NOT NULL,
        `email` varchar(200) NOT NULL,
        `name` varchar(200) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
       
      ALTER TABLE `customers` ADD PRIMARY KEY (`id`);
      ALTER TABLE `customers` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
      ALTER TABLE `customers` ADD UNIQUE (`email`);
      
EOD;
    $sth = $this->db->prepare($createTableSQL);
    $sth->execute();

    // Truncate and insert data
    $insertDataSQL = <<<EOD
    TRUNCATE TABLE `customers`;
    INSERT INTO `customers` (`email`, `name`) VALUES
    ('jane.doe@email.com', 'Jane Doe'),
    ('john.smith@email.com', 'John Smith');
EOD;
    $sth = $this->db->prepare($insertDataSQL);
    $sth->execute();

   // Redirect to index
   return $response->withRedirect('/');
});

$app->get('/truncate', function (Request $request, Response $response, array $args) {
    // Truncate data
    $sth = $this->db->prepare("TRUNCATE TABLE `customers`;");
    $sth->execute();

   // Redirect to index
   return $response->withRedirect('/');
});

$app->get('/', function (Request $request, Response $response, array $args) {
    // Retrieve all customers from the database
    $sth = $this->db->prepare("SELECT * FROM customers ORDER BY id");
    $sth->execute();
    $customers = $sth->fetchAll();

    // Render index view
    return $this->renderer->render($response, 'index.phtml', 
    [
        'customers' => $customers
    ]);
});

$app->get('/api/customers[/{email}]', function (Request $request, Response $response, $args) {
    if(!isset($args['email'])) {
        // Get all customers
        $sth = $this->db->prepare("SELECT * FROM customers ORDER BY id");
        $sth->execute();
        $customers = $sth->fetchAll();
        
        return $this->response->withJson($customers);
    }
    else {
        // Get customer by email
        $sth = $this->db->prepare("SELECT * FROM customers WHERE email=:email ORDER BY id");
        $sth->bindParam("email", $args['email']);
        $sth->execute();
        $customers = $sth->fetchAll();
        
        return $this->response->withJson($customers);
    }
});

$app->post('/customers', function (Request $request, Response $response, $args) {
    $body = $request->getParsedBody();
    
    $email = htmlspecialchars($body['email']);
    $name = htmlspecialchars($body['name']);
    

    if(empty($email) || empty($name)) {
        $this->logger->info("Empty name or email");
        return $response->withRedirect('/');    
    }

    // Insert customer
    $sth = $this->db->prepare("INSERT INTO customers (email,name) VALUES (:email,:name)");
    $sth->bindParam("email", $email);
    $sth->bindParam("name", $name);
    $sth->execute();

    $this->logger->info("Inserted id: " + $this->db->lastInsertId());

    // Redirect to index
    return $response->withRedirect('/');
});
