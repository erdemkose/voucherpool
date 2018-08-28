<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Traits;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use VoucherPool\DatabaseManager;

/**
 * Dependencies
 *
 * @package VoucherPool\Traits
 */
trait Dependencies {
    /**
     * Setup dependencies for Voucher Pool Application
     *
     * Since this trait is used in VoucherPool\App, we can access to the container by calling <code>$this->getContainer()</code>
     */
    private function setupDependencies()
    {
        $container = $this->getContainer();

        // change the default not found handler.
        $c['notFoundHandler'] = function (ContainerInterface $c) {
            return function (Request $request, Response $response) use ($c) {
                $logger = $c->get('logger');
                $logger->error('Requested path not found!', [
                    $request->getMethod(),
                    $request->getUri()->getPath(),
                    $request->getParams(),
                ]);

                return $response->withJson([
                    'error' => [
                        'message' => 'Requested path not found!',
                    ]
                ], 404);

            };
        };

        // change the default error handler.
        $container['errorHandler'] = function (ContainerInterface $c) {
            return function (Request $request, Response $response, \Exception $exception) use ($c) {
                $logger = $c->get('logger');
                $logger->error($exception->getMessage(), [
                    $request->getMethod(),
                    $request->getUri()->getPath(),
                    $request->getParams(),
                ]);

                // default HTTP response code
                $code = 500;
                $message = $exception->getMessage();

                // if the exception tries to set a valid HTTP response code, use it
                if($exception->getCode() > 100 && $exception->getCode() < 600) {
                    $code = $exception->getCode();
                }

                if (strpos($message, "SQLSTATE") !== false) {
                    $message = 'Unexpected database error!';
                }

                return $response->withJson([
                    'error' => [
                        'message' => $message,
                    ]
                ], $code);
            };
        };

        // use Monolog for logging
        $container['logger'] = function (ContainerInterface $c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
            return $logger;
        };

        // use Eloquent ORM
        // This dependency should be injected before each request. In order to achieve this, a middleware is used.
        $container['db'] = function (ContainerInterface $c) {
            return new DatabaseManager($c['settings']['db']);
        };
    }
}