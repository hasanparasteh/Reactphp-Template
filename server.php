<?php

// Methods
use App\Core\ErrorHandler;
use App\Core\JsonRequestDecoder;
use App\Core\Router;
use App\Helpers\JwtHelper;
use App\Methods\Health;
use App\Middleware\Guard;
use Dotenv\Dotenv;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use React\Http\HttpServer;
use React\MySQL\Factory;
use React\Socket\SocketServer;
use Sikei\React\Http\Middleware\CorsMiddleware;

require 'vendor/autoload.php';

// Setup EventLoop
$loop = React\EventLoop\Loop::get();

// Environments
$env = Dotenv::createImmutable(__DIR__);
$env->load();

$redisFactory = new Clue\React\Redis\Factory();


$DB_USER = $_ENV['DB_USER'];
$DB_PASS = $_ENV['DB_PASS'];
$DB_HOST = $_ENV['DB_HOST'];

$REDIS_HOST = $_ENV['REDIS_HOST'];
$REDIS_PORT = $_ENV['REDIS_PORT'];
@$REDIS_PASS = $_ENV['REDIS_PASSWORD'];

$redisConnection = $redisFactory->createLazyClient(
    empty($REDIS_PASS)
        ? sprintf("redis://%s:%s", $REDIS_HOST, $REDIS_PORT)
        : sprintf("redis://%s:%s?password=%s", $REDIS_HOST, $REDIS_PORT, $REDIS_PASS)
);

// Basic Redis
$redisServer = new Redis();
$redisServer->connect($REDIS_HOST, $REDIS_PORT);
if (!empty($REDIS_PASS))
    $redisServer->auth(['pass' => $REDIS_PASS]);


$jwtHelper = new JwtHelper($_ENV['JWT_KEY']);

$guard = new Guard($jwtHelper);

// Factories
$databaseFactory = new Factory($loop);

// Database Uri`s
$databaseUri = [
    'app' => $DB_USER . ':' . $DB_PASS . '@' . $DB_HOST . '/' . "app",
];

// Database Connections
$databaseConnections = [
    'app' => $databaseFactory->createLazyConnection($databaseUri['app']),
];

// Final Databases
$appManagerDatabase = new \App\Helpers\DatabaseHelper($databaseConnections['app']);


// Routes
$routes = new RouteCollector(new Std(), new GroupCountBased());

// Health Check
$routes->get("/", new Health());
$routes->get("/protected", $guard->protect(new Health()));

// Run Server
$server = new HttpServer(
    $loop,
    new ErrorHandler(),
    new CorsMiddleware([
        'allow_credentials' => true,
        'allow_origin' => ['*'],
        'allow_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
        'allow_headers' => ['DNT', 'X-Custom-Header', 'Keep-Alive', 'User-Agent', 'X-Requested-With', 'If-Modified-Since', 'Cache-Control', 'Content-Type', 'Content-Range', 'Range', 'Authorization'],
        'expose_headers' => ['DNT', 'X-Custom-Header', 'Keep-Alive', 'User-Agent', 'X-Requested-With', 'If-Modified-Since', 'Cache-Control', 'Content-Type', 'Content-Range', 'Range', 'Authorization'],
        'max_age' => 60 * 60 * 24 * 20
    ]),
    new JsonRequestDecoder(),
    new Router($routes)
);

// Listen Socket
$socket = new SocketServer('127.0.0.1:8000', [], $loop);
$server->listen($socket);

// Error Listener
$server->on(
    'error',
    function (Throwable $error) {
        echo 'Error: ' . $error->getMessage() . PHP_EOL;
    }
);

// Run
echo 'Listening on ' . str_replace('tcp', 'http', $socket->getAddress()) . PHP_EOL;
$loop->run();
