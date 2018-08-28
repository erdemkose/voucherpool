<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Traits;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Middlewares
 *
 * This trait is used by VoucherPool\App to register Slim Framework middlewares
 *
 * @package VoucherPool\Traits
 */
trait Middlewares
{
    /**
     * Use this function to register the middlewares
     *
     * Since this trait is used in VoucherPool\App, we can add a middleware by calling <code>$this->add()</code>
     */
    private function registerMiddlewares()
    {
        $container = $this->getContainer();

        // We need to boot Eloquent ORM before each request
        // Just accessing the db is enough
        $this->add(function(Request $request, Response $response, $next) use ($container){
            $container->get('db');
            return $next($request, $response);
        });
    }
}
