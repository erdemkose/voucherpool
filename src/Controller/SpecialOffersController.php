<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Controller;

use Psr\Container\ContainerInterface;

/**
 * Class SpecialOffersController
 * @package VoucherPool\Controller
 */
class SpecialOffersController extends ResourceController
{
    /**
     * SpecialOffersController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->className = '\\VoucherPool\\Model\\SpecialOffer';
    }
}