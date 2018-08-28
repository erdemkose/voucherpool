<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use VoucherPool\Model\Voucher;
use VoucherPool\Model\Recipient;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class RecipientsController
 *
 * @package VoucherPool\Controller
 */
class RecipientsController extends ResourceController
{
    /**
     * RecipientsController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->className = '\\VoucherPool\\Model\\Recipient';
    }

    /**
     * Get the vouchers of the recipient
     *
     * Vouchers are paged and filtered as in /vouchers request
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the recipient is not found
     */
    public function getVouchersByEmail(Request $request, Response $response, array $args): Response
    {
        $recipient = Recipient::where('email', $args['email'])->first();
        if($recipient == null) {
            throw new ModelNotFoundException('Recipient could not be found! Check if the e-mail address is correct.', 404);
        }

        $query = $recipient->vouchers()->getQuery();
        $resources = $this->getResourcesArray($query, $request, $response, $args);
        $resources['unused'] = $query->valid()->count();
        $resources['used'] = $resources['recordsTotal'] - $resources['unused'];
        $resources['today'] = $query->expiresToday()->count();

        return $response->withJson($resources, 200);
    }

    /**
     * Get the voucher by voucher code and recipient e-mail
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the recipient is not found
     */
    public function getVoucherByEmail(Request $request, Response $response, array $args): Response
    {
        $code = $args['code'];
        $email = $args['email'];

        /**
         * First we need to find the recipient by e-mail.
         *
         * If the recipient is not found an \Illuminate\Database\Eloquent\ModelNotFoundException is thrown.
         * This exception is handled by the container's errorHandler
         */
        $recipient = Recipient::where('email', $email)->first();
        if($recipient == null) {
            throw new ModelNotFoundException('Recipient could not be found! Check if the e-mail address is correct.', 404);
        }

        $voucher = $recipient->vouchers()->where('code',  $code)->first();
        if($voucher == null) {
            throw new ModelNotFoundException('Could not find a voucher with the provided code!', 404);
        }

        return $response->withJson($voucher, 200);
    }

    /**
     * Redeem the voucher by voucher code and recipient e-mail
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the recipient is not found
     */
    public function redeemVoucherByEmail(Request $request, Response $response, array $args): Response
    {
        $code = $args['code'];
        $email = $args['email'];

        /**
         * First we need to find the recipient by e-mail.
         *
         * If the recipient is not found an \Illuminate\Database\Eloquent\ModelNotFoundException is thrown.
         * This exception is handled by the container's errorHandler
         */
        $recipient = Recipient::where('email', $email)->first();
        if($recipient == null) {
            throw new ModelNotFoundException('Recipient could not be found! Check if the e-mail address is correct.', 404);
        }

        /**
         * A voucher MUST be used only once.
         * To avoid any race condition, voucher update must be executed in one atomic query or the table/row must be locked.
         * As a simple solution, an <code>UPDATE ... SET usage_date = $today WHERE usage_date IS NULL</code> query is executed.
         *
         * We may use INNODB Row Locking (SELECT ... FOR UPDATE) if we are using MySQL
         * */
        $affected_rows = Voucher::valid()
            ->where('code', $code)
            ->where('recipient_id', $recipient->id)
            ->update(['usage_date' => date("Y-m-d")]);


        /**
         * After voucher update query is executed, we check the number of rows affected.
         * If there is a valid voucher then this record gets updated and <code>$affected_rows</code> becomes 1
         *
         * If no row is affected, it means one of the following:
         *      a) the voucher is already used
         *      b) the voucher is expired
         *      c) the voucher belongs to another recipient
         *      d) the voucher with $code code could not be found
         */
        if ($affected_rows > 0) {
            $this->logger->info("Voucher is used: $code", [
                $request->getMethod(),
                $request->getUri()->getPath(),
                $request->getParams(),
            ]);

            $voucher = Voucher::where('code',  $code)->first();
            return $response->withJson($voucher, 200);
        } else {
            $voucher = Voucher::where('code',  $code)->first();
            if ($voucher) {
                $error_message = "";

                if ($voucher->usage_date != null) {
                    $error_message = 'The voucher is already used.';
                } elseif($voucher->is_expired) {
                    $error_message = "The voucher is expired on {$voucher->expiration_date}!";
                } elseif($voucher->recipient_id != $recipient->id) {
                    $error_message = 'The voucher belongs to another recipient!';
                }

                throw new \DomainException($error_message, 400);
            } else {
                throw new ModelNotFoundException('Could not find a voucher with the provided code!', 404);
            }
        }
    }
}