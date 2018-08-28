<?php
/**
 * Created by PhpStorm.
 * User: erdemkose
 * Date: 20.08.2018
 * Time: 00:01
 */

namespace Tests;

class SpecialOfferTest extends APIBaseTestCase
{
     public function testListSpecialOffers(): void
    {
        $response = $this->runApp('GET', '/api/v1/special-offers');
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, $responseJson->recordsTotal);
    }

    public function testCanCreateSpecialOffer(): void
    {
        $postdata = [
            'name' => 'Test testCanCreateSpecialOffer',
            'discount' => '0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($postdata['name'], $responseJson->name);
        $this->assertEquals($postdata['discount'], $responseJson->discount);
    }

    public function testCanNotCreateSpecialOfferWithEmptyName(): void
    {
        $postdata = [
            'name' => '',
            'discount' => '0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Special Offer name can not be empty!', $responseJson->error->message);
    }

    public function testCanNotCreateSpecialOfferWithTooLongName(): void
    {
        $postdata = [
            'name' => str_repeat('TooLongName', 30),
            'discount' => '0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Special Offer name can not be longer than 255 characters!', $responseJson->error->message);
    }

    public function testCanNotCreateSpecialOfferWithDuplicateName(): void
    {
        $postdata = [
            'name' => 'testCanNotCreateSpecialOfferWithDuplicateName',
            'discount' => '0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($postdata['name'], $responseJson->name);
        $this->assertEquals($postdata['discount'], $responseJson->discount);

        $postdata['discount'] = '0.60';
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('SpecialOffer already exists!', $responseJson->error->message);
    }

    public function testCanNotCreateSpecialOfferWithEmptyDiscount(): void
    {
        $postdata = [
            'name' => 'Test testCanNotCreateSpecialOfferWithEmptyDiscount',
            'discount' => '',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Discount can not be empty!', $responseJson->error->message);
    }

    public function testCanNotCreateSpecialOfferWithOutOfRangeDiscount(): void
    {
        $postdata = [
            'name' => 'Test testCanNotCreateSpecialOfferWithInvalidDiscount',
            'discount' => '-0.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Discount must be between 0 and 1!', $responseJson->error->message);

        $postdata = [
            'name' => 'Test testCanNotCreateSpecialOfferWithInvalidDiscount',
            'discount' => '1.50',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Discount must be between 0 and 1!', $responseJson->error->message);
    }

    public function testCanNotCreateSpecialOfferWithInvalidDiscount(): void
    {
        $postdata = [
            'name' => 'Test testCanNotCreateSpecialOfferWithInvalidDiscount',
            'discount' => 'x.y',
        ];
        $response = $this->runApp('POST', '/api/v1/special-offers', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Discount MUST be numeric!', $responseJson->error->message);
    }
}
