<?php

namespace App;

use App\Helpers\ResponseHelper;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Capsule\Manager as Capsule;

class Application
{
    /**
     * @var Manager
     */
    public Manager $db;

    public function __construct()
    {
        // Get .env loader
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        // Set DB Connection
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_NAME'),
            'username' => env('DB_USER'),
            'password' => env('DB_PASS'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * @param array $arguments
     * @return void
     * @throws \Exception
     */
    public function executeConsoleCommand(array $arguments): void
    {
        $commandClass = 'App\\Command\\' . $arguments[1];

        if (!class_exists($commandClass)) {
            exit("Invalid command!\n");
        }

        $commandObject = new $commandClass();
        $commandObject->handle();
    }

    /**
     * @param string $action
     * @return void
     * @throws \Exception
     */
    public function executeApiAction(string $action): void
    {
        $parts = explode('/', $action);

        try {
            $controller = 'App\\Controller\\' . ucfirst($parts[0]) . 'Controller';

            //unset($parts[0]);

            if (!class_exists($controller)) {
                throw new \Exception('Invalid controller!');
            }

            if (!isset($parts[1])) {
                $method = 'index';
            } else {
                $method = strtolower($parts[1]);

                //unset($parts[1]);
            }

            $controllerObject = new $controller();

            if (!method_exists($controllerObject, $method)) {
                throw new \Exception('Invalid controller method!');
            }

            // TODO: Add PUT in future as well
            // Pass GET and POST data to the controller action.
            $params = array_merge($_GET, $_POST);

            if (isset($params['action'])) {
                unset($params['action']);
            }

            $result = $controllerObject->{$method}((array) $params);

            header("Content-type: application/json; charset=utf-8");
            http_response_code(200);
            echo $result;
        } catch (\Throwable $e) {
            header("Content-type: application/json; charset=utf-8");
            http_response_code(500);
            echo ResponseHelper::formatResponseError($e);
        }
    }
}