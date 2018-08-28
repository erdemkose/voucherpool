<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace Tests;

class VoucherTest extends APIBaseTestCase
{
    private function createTestSpecialOffer()
    {
        $testSpecialOffersCount = count($this->testData['testSpecialOffers']);

        // Create a special offer
        $postdata = [
            'name' => 'Test createTestSpecialOffer '. $testSpecialOffersCount,
            'discount' => '0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $specialOffer = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertAttributeGreaterThan(0, 'id', $specialOffer);


        $this->testData['testSpecialOffers'][] = $specialOffer;
        return $specialOffer;
    }

    private function createTestRecipient()
    {
        $testRecipientsCount = count($this->testData['testRecipients']);

        // Create a recipient
        $postdata = [
            'name' => 'Test createTestRecipient '. $testRecipientsCount,
            'email' => 'createTestRecipient'. $testRecipientsCount .'@example.com',
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $recipient = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertAttributeGreaterThan(0, 'id', $recipient);

        $this->testData['testRecipients'][] = $recipient;
        return $recipient;
    }

    private function createTestVoucher($recipient_ids, $special_offer_id, $expiration_date)
    {
        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $vouchers = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());

        return $vouchers;
    }

    public function testCanCreateVoucherWithSingleRecipient(): void
    {
        // To create a voucher we need at least one Recipient and one Special Offer
        $recipient = $this->createTestRecipient();
        $specialOffer = $this->createTestSpecialOffer();

        // Now we can try to create a Voucher
        $expiration_date = date('Y-m-d');
        $recipient_ids = [$recipient->id];
        $special_offer_id = $specialOffer->id;

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $vouchers = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        for($i = 0; $i < count($recipient_ids); $i++) {
            $voucher = $vouchers[$i];

            $this->assertEquals($expiration_date, $voucher->expiration_date);
            $this->assertEquals($special_offer_id, $voucher->special_offer_id);
            $this->assertEquals($recipient_ids[$i], $voucher->recipient_id);
        }
    }

    public function testCanCreateVoucherWithMultipleRecipient(): void
    {
        // To create a voucher we need at least one Recipient and one Special Offer
        $recipient1 = $this->createTestRecipient();
        $recipient2 = $this->createTestRecipient();
        $recipient3 = $this->createTestRecipient();
        $specialOffer = $this->createTestSpecialOffer();

        // Now we can try to create a Voucher
        $expiration_date = date('Y-m-d');
        $recipient_ids = [$recipient1->id, $recipient2->id, $recipient3->id];
        $special_offer_id = $specialOffer->id;

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $vouchers = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        for($i = 0; $i < count($recipient_ids); $i++) {
            $voucher = $vouchers[$i];

            $this->assertEquals($expiration_date, $voucher->expiration_date);
            $this->assertEquals($special_offer_id, $voucher->special_offer_id);
            $this->assertEquals($recipient_ids[$i], $voucher->recipient_id);
            $this->assertNotEmpty($voucher->code);
        }
    }

    public function testCanNotCreateVoucherWithNoRecipients(): void
    {
        $expiration_date = date('Y-m-d');
        $recipient_ids = [];
        $special_offer_id = 1;

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $vouchers = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertCount(0, $vouchers);
    }

    public function testCanNotCreateVoucherWithInvalidRecipients(): void
    {
        $specialOffer = $this->createTestSpecialOffer();

        $expiration_date = date('Y-m-d');
        $recipient_ids = ['b', 'a'];
        $special_offer_id = $specialOffer->id;

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Voucher recipient id MUST be an integer!', $responseJson->error->message);
    }

    public function testCanNotCreateVoucherWithNonExistingRecipients(): void
    {
        $specialOffer = $this->createTestSpecialOffer();

        $expiration_date = date('Y-m-d');
        $recipient_ids = [32, 64];
        $special_offer_id = $specialOffer->id;

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Could not find the recipient!', $responseJson->error->message);
    }

    public function testCanNotCreateVoucherWithNoSpecialOffers(): void
    {
        $recipient = $this->createTestRecipient();

        $expiration_date = date('Y-m-d');
        $recipient_ids = [$recipient->id];
        $special_offer_id = '';

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Voucher special offer id MUST be an integer!', $responseJson->error->message);
    }

    public function testCanNotCreateVoucherWithInvalidSpecialOffers(): void
    {
        $recipient = $this->createTestRecipient();

        $expiration_date = date('Y-m-d');
        $recipient_ids = [$recipient->id];
        $special_offer_id = 'a';

        $postdata = [
            'expiration_date' => $expiration_date,
            'special_offer_id' => $special_offer_id,
            'recipient_ids' => $recipient_ids,
        ];
        $response = $this->runApp('POST', '/api/v1/vouchers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Voucher special offer id MUST be an integer!', $responseJson->error->message);
    }

    public function testRedeemValidVoucher(): void
    {
        $recipient = $this->createTestRecipient();
        $specialOffer = $this->createTestSpecialOffer();
        $vouchers = $this->createTestVoucher([$recipient->id], $specialOffer->id, date('Y-m-d'));

        $this->assertCount(1, $vouchers);
        $this->assertAttributeNotEmpty('code', $vouchers[0]);

        $email = $recipient->email;
        $code = $vouchers[0]->code;
        $response = $this->runApp('PUT', '/api/v1/recipients/'. $email .'/vouchers/'. $code);
        $voucher = json_decode((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertAttributeGreaterThan(0, 'id', $voucher);
        $this->assertAttributeEquals(true, 'is_used', $voucher);
    }

    public function testRedeemVoucherWithWrongCode(): void
    {
        $recipient = $this->createTestRecipient();
        $specialOffer = $this->createTestSpecialOffer();
        $vouchers = $this->createTestVoucher([$recipient->id], $specialOffer->id, date('Y-m-d'));

        $this->assertCount(1, $vouchers);
        $this->assertAttributeNotEmpty('code', $vouchers[0]);

        $email = $recipient->email;
        $code = $vouchers[0]->code .'XYZ';
        $response = $this->runApp('PUT', '/api/v1/recipients/'. $email .'/vouchers/'. $code);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Could not find a voucher with the provided code!', $responseJson->error->message);
    }

    public function testRedeemVoucherWithWrongEmail(): void
    {
        $recipient = $this->createTestRecipient();
        $specialOffer = $this->createTestSpecialOffer();
        $vouchers = $this->createTestVoucher([$recipient->id], $specialOffer->id, date('Y-m-d'));

        $this->assertCount(1, $vouchers);
        $this->assertAttributeNotEmpty('code', $vouchers[0]);

        $email = $recipient->email. 'XYZ';
        $code = $vouchers[0]->code;
        $response = $this->runApp('PUT', '/api/v1/recipients/'. $email .'/vouchers/'. $code);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Recipient could not be found! Check if the e-mail address is correct.', $responseJson->error->message);
    }
}
