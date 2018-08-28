<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace Tests;

class RecipientTest extends APIBaseTestCase
{
    public function testListRecipients(): void
    {
        $response = $this->runApp('GET', '/api/v1/recipients');
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(0, $responseJson->recordsTotal);
    }

    public function testCanCreateRecipient(): void
    {
        $name = 'Test CanCreateRecipient';
        $email = 'testCanCreateRecipient@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        // Recipient should be created and the new Recipient should be returned
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($name, $responseJson->name);
        $this->assertEquals($email, $responseJson->email);
        $this->assertEquals(1, $responseJson->id);

        // Get the list of Recipients
        $response = $this->runApp('GET', '/api/v1/recipients');
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $responseJson->recordsTotal);

        $recipient = $responseJson->data[0];
        $this->assertEquals($name, $recipient->name);
        $this->assertEquals($email, $recipient->email);
        $this->assertEquals(1, $recipient->id);
    }

    public function testCanNotCreateRecipientWithEmptyName(): void
    {
        $name = '';
        $email = 'testCanNotCreateRecipientWithEmptyName@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Recipient name can not be empty!', $responseJson->error->message);
    }

    public function testCanNotCreateRecipientWithForbiddenCharacters(): void
    {
        $name = '#Test #User';
        $email = 'testCanNotCreateRecipientWithForbiddenCharacters@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Recipient name can only contain letters, numbers and space character!', $responseJson->error->message);
    }

    public function testCanNotCreateRecipientWithTooLongName(): void
    {
        $name = 'This line is only seventy characters long so we need four of this line';
        $name .= $name;
        $name .= $name;
        $name .= $name;
        $email = 'testCanNotCreateRecipientWithTooLongName@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Recipient name can not be longer than 255 characters!', $responseJson->error->message);
    }

    public function testCanNotCreateRecipientWithInvalidEmail(): void
    {
        $name = 'Test User';
        $email = 'testCanNotCreateRecipientWithInvalidEmail*example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Recipient e-mail address is not valid!', $responseJson->error->message);
    }

    public function testCanNotCreateRecipientWithDuplicateEmail(): void
    {
        $name = 'Test User 1';
        $email = 'testCanNotCreateRecipientWithDuplicateEmail@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($name, $responseJson->name);
        $this->assertEquals($email, $responseJson->email);

        $name = 'Test User 2';
        //$email = 'testCanNotCreateRecipientWithDuplicateEmail@example.com';

        $postdata = [
            'name' => $name,
            'email' => $email,
        ];
        $response = $this->runApp('POST', '/api/v1/recipients', $postdata);
        $responseJson = json_decode((string)$response->getBody());

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Recipient already exists!', $responseJson->error->message);
    }
}
