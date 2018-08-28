<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool;

use Illuminate\Database\Capsule\Manager;

/**
 * DatabaseManager
 *
 * @package VoucherPool
 */
class DatabaseManager extends Manager
{
    /**
     * DatabaseManager constructor.
     *
     * @param array $settings required settings for Eloquent ORM
     *
     * @throws \RuntimeException if the database file can not be created.
     */
    public function __construct($settings)
    {
        parent::__construct();

        //To create an SQLite database, we need to create a new file to store the database.
        //If the database file already exists, then we need to remove the initialization commands.
        if ($settings['driver'] == 'sqlite' && $settings['database'] != ':memory:') {
            if (file_exists($settings['database']) === false) {
                $file_created = touch($settings['database']);
                if ($file_created === false) {
                    throw new \RuntimeException("Could not create database file for SQLite!", 500);
                }
            } else {
                unset($settings['create_sql']);
            }
        }

        $this->addConnection($settings);
        $this->setAsGlobal();
        $this->bootEloquent();

        if (isset($settings['create_sql'])) {
            $sql = file_get_contents($settings['create_sql']);
            $this->getConnection()->unprepared($sql);
        }

        if (isset($settings['init_sql'])) {
            $sql = file_get_contents($settings['init_sql']);
            $this->getConnection()->unprepared($sql);
        }
    }
}