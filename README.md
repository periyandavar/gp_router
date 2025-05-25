# GP Router

The GP Router is a lightweight and flexible routing solution for PHP applications. It simplifies the process of mapping HTTP requests to specific controllers or callbacks, enabling developers to build clean, organized, and scalable web applications. With its modular design, the library supports dynamic route parameters, middleware (filters) for pre- and post-processing, and API-specific routes. It is suitable for a wide range of applications, from simple websites to complex RESTful APIs.

This library focuses on:
- **Ease of use**: Define routes with minimal setup.
- **Flexibility**: Handle dynamic URL parameters and custom middleware.
- **Extensibility**: Support for API-specific routes and customizable request/response handling.

Whether you are building a small project or a large-scale application, the GP Router library provides the tools you need to manage your application's routing efficiently.

---
## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Features](#features)
- [Classes](#classes)
  - [Router](#router)
  - [Route](#route)
  - [APIRoute](#apiroute)
  - [Wrapper](#wrapper)
  - [Request](#request)
  - [Response](#response)
  - [Filter](#filter)
- [Usage](#usage)
  - [Basic Route Definition](#basic-route-definition)
  - [Using Route Class](#using-route-class)
  - [Dynamic Parameters in Routes](#dynamic-parameters-in-routes)
  - [Using Filters (Middleware)](#using-filters-middleware)
  - [Auto Resolve params](#auto-resolve-params)
    - [Resolving the Request & Response class](#resolving-the-request--response-class)
    - [Resolving the url params](#resolving-the-url-params)
    - [Resolving the Modal class](#resolving-the-modal-class)
    - [Resolving the other service/model](#resolving-the-other-servicemodel)
- [Example](#example)
   - [Initializing the Router](#initializing-the-router)
   - [Using Request object](#using-request-object)
   - [Using Response object](#using-response-object)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)
- [Author](#author)

---


## Requirements

- PHP 7.4 or higher.
- Composer (optional but recommended for autoloading).

---


## Installation

You can install `gp_router` using Composer. Run the following command in your terminal:

```
composer require gp/router
```
---

## Getting Started

After installation, you can start using the package by including the autoloader:

```
require 'vendor/autoload.php';
```
---

## Features

The GP Router library provides a comprehensive set of features to handle routing efficiently in PHP applications:

- **Flexible Route Definitions**:
   - Define static and dynamic routes with ease.
   - Support for URL parameters (e.g., `/user/{(\d+):id}`).

- **HTTP Method Handling**:
   - Supports all major HTTP methods: `GET`, `POST`, `PUT`, `DELETE`, `PATCH`, etc.
   - Route-specific method restrictions.

- **Middleware Support (Filters)**:
   - Add pre- or post-processing logic to routes using filters.
   - Useful for authentication, logging, and request validation.

- **API-Specific Routing**:
   - Use the `APIRoute` class to define API endpoints.
   - Designed for building RESTful services.

- **Supports Dependency Injection and Auto Param Resolve**
    - Automatically resolve the controller class constructor and action method params with query params, request and response instance, service instances and also model class with post data

- **Error Handling**:
   - Customizable error handlers for unmatched routes or invalid requests.

- **Request and Response Abstraction**:
   - Simplifies access to HTTP request data (GET, POST, headers, etc.).
   - Facilitates response construction, including status codes and headers.

- **Route Groups and Prefixes**:
   - Group multiple routes with a shared prefix.
   - Useful for organizing application modules (e.g., `/api/v1/`).

- **Named Routes**:
   - Assign names to routes for easier URL generation.

- **Dynamic URL Matching**:
   - Match routes using regular expressions or placeholders.

- **Extensibility**:
    - Easily extend core classes to add custom functionality.
    - Seamlessly integrate with other libraries or frameworks.

- **Lightweight and Fast**:
    - Designed with performance in mind, making it ideal for high-traffic applications.

- **Error-Free Execution**:
    - Built-in validation and error handling ensure smooth routing logic.

These features make the GP Router library a powerful and versatile choice for managing routing in your PHP projects. 

---

## Classes

### `Router`

Handles routing functionality.

#### Key Methods:
- `add($route, $expression, $method, $filter, $name)`: Adds a route.
- `run($caseSensitive, $url, $method)`: Executes the router.
- `error($data, $code)`: Handles errors which call the configured controller for errors.

---

### `Route`

Represents a single route.

#### Key Properties:
- `$path`/`$rule` : Route path (eg: `user/{(\d+):id}`).
- `$expression`: Controller and action method or callback
  - home/index - invokes HomeController::index()
  - home - invokes HomeController::invoke()
  - callback function - call the callback function
- `$method`: HTTP method.
- `$filter`: Array of callback or Filter instances to be executed as middleware.
- `$name`: Route name.

---

### `APIRoute`

Handles API-specific routes.

| Method                  | Description                                                                 | Parameters                                                                 |
|-----------------------------|---------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| __construct()               | Create a necessary REST API route for the entity                                | $rule, $apiClass, $filters =[], $name                                                                          |


---

### `Wrapper`

Wraps multiple routes together.

| Method                  | Description                                                                 | Parameters                                                                 |
|-----------------------------|---------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| getRoutes()               | Retrieves the list of routes within the wrapper.                                | None                                                                          |

---

### `Request`

Handles HTTP request details.

 Method                  | Description                                                                 | Parameters                                                                 |
|-----------------------------|---------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| get()                     | Retrieves all GET parameters from the request.     | $key - the key name (if empty fetch all), $default - default value to be returned null by default                            | 
| urlParam()                     | Retrieves all url parameters from the request.                                  | $key - the key name (if empty fetch all), $default - default value to be returned null by defaultNone                                                                          |
| post()                    | Retrieves all POST parameters from the request.                                 | $key - the key name (if empty fetch all), $default - default value to be returned null by defaultNone                                                                          |
| data()                    | Retrieves JSON request body for REST API                           | $key - the key name (if empty fetch all), $default - default value to be returned null by defaultNone                                                                          |
| session()                     | Retrieves all SESSION parameters from the request.                                  | $key - the key name, $default - default value to be returned null by default|
| cookies()                    | Retrieves all COOKIES parameters from the request.                                 | $key - the key name, $default - default value to be returned null by default                                   |
| headers()                    | Retrieves all header values    | $key - the key name (if empty fetch all), $default - default value to be returned null by default|
| server()                    | Retrieves all server values    | $key - the key name, $default - default value to be returned null by default |

---

### `Response`

Handles HTTP response generation.

| Method                  | Description                                                                 | Parameters                                                                 |
|-----------------------------|---------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| setStatusCode()           | Sets the HTTP status code for the response.                                     | $code (int)                                                                |
| setBody()                 | Sets the body content of the response.                                          | $body (string)                                                             |

---

### `Filter`

Represents middleware for routes.

| Method                  | Description                                                                 | Parameters                                                                 |
|-----------------------------|---------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| apply()                   | Applies the filter logic to the given request and response.                     | $request (Request), $response (Response)                 

---

## Usage

### Basic Route Definition

```
use Router\Router;

Router::add('/about', 'AboutController/show', $router::METHOD_GET); 
Router::run();
```

### Using Route Class

```
use Router\Router;
use Router\Route;

$route = new Route('/about', 'AboutController/show', $router::METHOD_GET);
Router::addRoute($route);
Router::run();
```

### Dynamic Parameters in Routes

```

Router::add('/user/{(\d+):id}', 'UserController/show', $router::METHOD_GET); Router::run();
```
In this example, `{id}` is a dynamic parameter. You can access it in your controller using the `Request` object.

### Using Filters (Middleware)

```
use Router\Route;
use Router\Filter\Filter;

class AuthFilter implements Filter
{
    public function filter(Request $request, Response $response): void
    {
        if (!$request->get('token')) {
          return false;
        }
    }
}

$route = new Route('/secure', 'SecureController/index', Router::METHOD_GET, [new AuthFilter()]); $router = new Router();
Router::addRoute($route);
Router::run();
```

### Auto Resolve params

#### Resolving the Request & Response class

The request & response class object will be resolved and passed automatically to the controller consturctor or action method without any additional config, you can use them by adding them as param in the constructor or method, please ensure that you have added exact type for the params.

##### In constructor Param
  
```
use Router\Request;
use Router\Response;

class APIController
{
   private $req;
   private $res;

   public function __construct(Request $request, Response $res)
   {
     $this->req = $request;
     $this->res = $response;
   }

   .....
}
```

##### In Method Param
  
```
use Router\Request;
use Router\Response;

class APIController
{
   public function getPage(Request $req, Response $res)
   {
      .....
   }

   .....
}
```

#### Resolving the url params

You can also add the url param values as the part of the param in the constructor or in class method.

-  config the url param name in the route as follow. note: path or rule should have the url name and its regex as `{(regex):key}`
  ```
  Router::add('/user/{(\d+):id}', 'UserController/show', $router::METHOD_GET); 
  ```
- Add this url param name as parameter name as follows

```
use Router\Request;
use Router\Response;

class APIController
{
   public function getPage(Request $req, Response $res, int $id)
   {
      .....
   }

   .....
}
```

#### Resolving the Modal class
  You can configure the modal class to capture the request data and it's instance will be passed as the param
  
  - Create a base modal class
  - Create derived modal class from this base modal class which needs to be passed as parameter.
  - set the base modal class to the Router using setUpModalClass
  - Add a derived modal class as a parameter to the method.

```

  class Modal {

  }

  class User extends Modal {
    public $name;
    public $age;
  }

  class UserController
  {
    public function process(User $user) {
      print_r([
        'name' => $user->name,
        'age' => $user->age,
      ]);
    }
  }

  Router::setUpModalClass(Modal::class); // add a base type of the modal class
  Router::run();

```

#### Resolving the other service/model
  In the same way we can resolve other service modal class instances.


## Example

### Initializing the Router

You can initialize the router and define routes in your application:

```
use Router\Router;

// Create a router instance
$router = new Router();

// Define routes
Router::add('/home', 'HomeController/index', $router::METHOD_GET);
Router::add('user/{(\d+):id}', 'UserController/show', $router::METHOD_GET);
Router::add('/api/data', 'APIController/getData', $router::METHOD_POST);

// Run the router
Router::run();
```

### Using Request object

once the request object is passed as the param to the action method, we can use this method to get all the required details of the request using available methods in the request class.

```
function process(Request $req)
{
  $data = [
    'get' => $req->get(),
    'post' => $req->post(),
    'data' => $req->data(),
    'urlParam' => $req->urlParam(),
    'session' => $req->session('name),
    'cookie' => $req->cookie('name'),
    'server' => $req->server('name'),
    'header' => $req->header('name')
  ];

  print_r($data);
}

```

### Using Response object

once the response object is passed as the param to the action method, we can use this to handle the response.

The response content will be captured by
  - The return values of the method (or)
  - The content set in Response object

```
function process(Request $req, Response $res)
{
  $data = [
    'get' => $req->get(),
    'post' => $req->post(),
    'data' => $req->data(),
    'urlParam' => $req->urlParam(),
    'session' => $req->session('name),
    'cookie' => $req->cookie('name'),
    'server' => $req->server('name'),
    'header' => $req->header('name')
  ];

  // way 1 
  return $data; // Automatically Router will set this content to Response class

  // way 2
  $res->setBody($data); // The Router automatically fetch the response content and process it.

  //way3
  $res->setBody($data);
  return $res;

}
```

we can also change the response type by using the setType method in response class as follows

```
$response->setStatusCode(200)
  ->setContent('welcome')
  ->setType(Response::TYPE_HTML);

```

Available response types are as follows

- JSON
- HTML
- XML
- text
- csv
- yaml
- binary
- image
- audio
- video
- stream

---




## Contributing

Contributions are welcome! If you would like to contribute to gp_validator, please follow these steps:

- Fork the repository.
- Create a new branch (git checkout -b feature/- YourFeature).
- Make your changes and commit them (git commit -m 'Add some feature').
- Push to the branch (git push origin feature/YourFeature).
- Open a pull request.
- Please ensure that your code adheres to the coding standards and includes appropriate tests.

---

## License

This package is licensed under the MIT License. See the [LICENSE](https://github.com/periyandavar/gp_router/blob/main/LICENSE) file for more information.

---

## Contact
For questions or issues, please reach out to the development team or open a ticket.

---


## Author

- Periyandavar [Github](https://github.com/periyandavar) (<vickyperiyandavar@gmail.com>)

---