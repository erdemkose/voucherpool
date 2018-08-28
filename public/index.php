<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

require __DIR__ . '/../vendor/autoload.php';

/**
 * Production settings
 *
 * Be sure that database and log directories are writable
 */
$settings = [
    'settings' => [
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'displayErrorDetails' => true,

        // Database settings. For easier development SQLite is used
        'db' => [
            'driver'     => 'sqlite',
            'prefix'     => '',
            'database'   => __DIR__ .'/../db/voucherpool-production.sqlite',
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

$app = new \VoucherPool\App($settings);
$app->run();
