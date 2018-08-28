<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Controller;

use VoucherPool\Model\Voucher;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class VouchersController
 * @package VoucherPool\Controller
 */
class VouchersController extends ResourceController
{
    /**
     * How many times should we try to find a unique voucher code in the database
     */
    private const UNIQUE_CODE_TRIAL = 5;

    /**
     * VouchersController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->className = '\\VoucherPool\\Model\\Voucher';
    }

    /**
     * Creates a new Voucher for each Recipient
     *
     * Rollbacks if any exception is thrown
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        // All the vouchers will be created as one party or no vouchers will be created
        // So we need to wrap function with a database transaction
        $db = $this->container->get('db');
        $db->getConnection()->beginTransaction();

        // Create a copy of the request body to use in Voucher creation
        $attributes = $body = $request->getParsedBody();

        // This value is not used by Voucher so we need to unset it
        unset($attributes['recipient_ids']);

        try
        {
            $vouchers = [];
            foreach($body['recipient_ids'] as $recipient_id) {
                $attributes['recipient_id'] = $recipient_id;

                // A voucher with the same random code may already be in the database.
                // We will try to find a unique code in UNIQUE_CODE_TRIAL trials.
                // If a unique code could not be found, throw an Exception.
                for($i = 0; $i < self::UNIQUE_CODE_TRIAL; $i++) {
                    try {
                        $voucher = new Voucher();
                        $voucher->fill($attributes); // Values in $attributes are validated in the setters
                        $voucher->code = Voucher::generateRandomCode();
                        $voucher->save();
                        $vouchers[] = $voucher;
                        break;
                    }
                    catch (\PDOException $exception)
                    {
                        //Throw an exception if we reached the trials limit
                        if($i == self::UNIQUE_CODE_TRIAL) {
                            throw new \RuntimeException("Too many trials to find a unique code", 500);
                        }
                    }
                    catch(\InvalidArgumentException|\RuntimeException $exception)
                    {
                        // If an attribute value is invalid then we need to throw an exception
                        throw new \RuntimeException($exception->getMessage(), 400);
                    }
                }
            }

            // No problem occurred while creating the vouchers so we can commit
            $db->getConnection()->commit();
            return $response->withJson($vouchers, 201);
        }
        catch (\Exception $exception)
        {
            // Cancel the whole voucher creation process
            $db->getConnection()->rollback();

            throw $exception;
        }
    }

    /**
     * Returns Vouchers
     *
     * Add additional information to parent response
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param array $args
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $query = Voucher::where('1', '1');
        $resources = $this->getResourcesArray($query, $request, $response, $args);
        $resources['unused'] = Voucher::valid()->count();
        $resources['used'] = $resources['recordsTotal'] - $resources['unused'];
        $resources['today'] = Voucher::expiresToday()->count();

        return $response->withJson($resources, 200);
    }
}