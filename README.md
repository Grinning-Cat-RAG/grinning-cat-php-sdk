# GrinningCat PHP SDK

----

**Grinning Cat PHP SDK** is a library to help the implementation
of [Grinning Cat](https://github.com/matteocacciola/grinning-cat-core) on a PHP Project

* [Installation](#installation)
* [Usage](#usage)

## Installation

To install GrinningCat PHP SDK you can run this command:
```cmd
composer require matteocacciola/grinning-cat-php-sdk
```

## Usage
Initialization and usage:

```php
use DataMat\GrinningCat\GrinningCatClient;
use DataMat\GrinningCat\Clients\HttpClient;
use DataMat\GrinningCat\Clients\WSClient;

$grinningCatClient = new GrinningCatClient(
    new WSClient('grinning_cat_core', 1865, null),
    new HttpClient('grinning_cat_core', 1865, null)
);
```

Authenticate with a username and password (the client keeps the returned access token for the following calls),
refresh it before it expires, and log out to revoke the session:

```php
$token = $grinningCatClient->auth()->token('username', 'password');
// $token->accessToken, $token->expiresIn, $token->refreshToken, $token->refreshExpiresIn

// before the access token expires, exchange the (single-use) refresh token for a new pair
$token = $grinningCatClient->auth()->refresh($token->refreshToken);

// revoke the session; already-issued access tokens stay valid until they expire
$grinningCatClient->auth()->logout($token->refreshToken);
```
Send a message to the websocket:

```php
$notificationClosure = function (string $message) {
    // handle websocket notification, like chat token stream
}

// result is the result of the message
$result = $grinningCatClient->message()->sendWebsocketMessage(
    new Message("Hello world!", 'user', []),  // message body
    $notificationClosure // websocket notification closure handle
);

```

Load data to the rabbit hole:
```php
//file
$promise = $grinningCatClient->rabbitHole()->postFile($uploadedFile->getPathname());
$promise->wait();

//url
$promise = $grinningCatClient->rabbitHole()->postWeb($url);
$promise->wait();
```

Memory management utilities:

```php
$grinningCatClient->memory()->getMemoryCollections(); // get number of vectors in the working memory
$grinningCatClient->memory()->getMemoryRecall("HELLO"); // recall memories by text

//delete memory points by metadata, like this example delete by source
$grinningCatClient->memory()->deleteMemoryPointsByMetadata(Collection.Declarative, ["source" => $url]);
```
