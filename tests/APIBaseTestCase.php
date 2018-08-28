<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

abstract class APIBaseTestCase extends TestCase
{
    /**
     * Voucher Pool application settings modified for testing
     * @var array
     */
    private $settings = [
        'settings' => [
            'addContentLengthHeader' => false, // Allow the web server to send the content-length header
            'displayErrorDetails' => true,

            'db' => [
                'driver'     => 'sqlite',
                'prefix'     => '',
                'database'   => ':memory:',
                'create_sql' => __DIR__ .'/../db/voucherpool-sqlite-create.sql',
                'init_sql'   => __DIR__ .'/../db/voucherpool-sqlite-init.sql',
            ],

            // Monolog settings
            'logger' => [
                'name' => 'voucherpool',
                'path' => __DIR__ .'/../logs/app.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
    ];

    /**
     * @var \VoucherPool\App
     */
    private $app;

    /**
     * @var array Temporary variable to keep created resources during test
     */
    protected $testData;

    /**
     * Reset App before each test
     */
    protected function setUp()
    {
        $this->testData = [];
        $this->testData['testRecipients'] = [];
        $this->testData['testSpecialOffers'] = [];

        // Instantiate the application
        $this->app = new \VoucherPool\App($this->settings);
    }

    /**
     * Destroy Slim App after each test
     */
    protected function tearDown()
    {
        $this->app = null;
    }

    /**
     * Create a mock environment and process the request
     *
     * @param $requestMethod
     * @param $requestUri
     * @param null|array $requestData
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock([
            'REQUEST_METHOD' => $requestMethod,
            'REQUEST_URI' => $requestUri
        ]);

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Process the application
        $response = $this->app->process($request, $response);

        // Return the response
        return $response;
    }
}