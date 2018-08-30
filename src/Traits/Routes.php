<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Traits;

use VoucherPool\Controller\VouchersController;
use VoucherPool\Controller\RecipientsController;
use VoucherPool\Controller\SpecialOffersController;

trait Routes
{
    /**
     *
     * @throws \InvalidArgumentException if an API parameter is invalid
     */
    private function registerRoutes()
    {
        $this->group('/api/v1', function () {

            // Create routes for CRUD operations
            // Currently we do not support Update
            $this->map(['GET', 'POST', 'DELETE'], '/vouchers[/{id}]', VouchersController::class);
            $this->map(['GET', 'POST', 'DELETE'], '/recipients[/{id}]', RecipientsController::class);
            $this->map(['GET', 'POST', 'DELETE'], '/special-offers[/{id}]', SpecialOffersController::class);

            // Return list of vouchers of a specific recipient
            $this->get('/recipients/{email}/vouchers',
                RecipientsController::class .':getVouchersByEmail');

            // Redeem a voucher of a specific recipient
            $this->get('/recipients/{email}/vouchers/{code}',
                RecipientsController::class .':getVoucherByEmail');

            // Redeem a voucher of a specific recipient
            $this->patch('/recipients/{email}/vouchers/{code}',
                RecipientsController::class .':redeemVoucherByEmail');
        });
    }
}