<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool;

/**
 * Voucher Pool App
 *
 * App components can be modified in related Traits.
 *
 * @package VoucherPool
 */
class App extends \Slim\App
{
    use Traits\Dependencies;
    use Traits\Middlewares;
    use Traits\Routes;

    /**
     * Create new Voucher Pool App
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        parent::__construct($settings);

        // Set up dependencies
        $this->setupDependencies();

        // Register middleware
        $this->registerMiddlewares();

        // Register routes
        $this->registerRoutes();
    }
}