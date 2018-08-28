/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

DROP TABLE IF EXISTS 'recipients';
DROP TABLE IF EXISTS 'special_offers';
DROP TABLE IF EXISTS 'vouchers' ;

/**
 * Recipients Table
 *
 * "email" field is unique
 */
CREATE TABLE 'recipients' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  'name' TEXT NOT NULL,
  'email' TEXT NOT NULL UNIQUE
);

/**
 * Special Offers Table
 *
 * "name" field is unique
 */
CREATE TABLE 'special_offers' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  'name' TEXT NOT NULL UNIQUE,
  'discount' NUMERIC NOT NULL
);

/**
 * Vouchers Table
 *
 * "code" field is unique
 */
CREATE TABLE 'vouchers' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  'code' TEXT NOT NULL UNIQUE,
  'expiration_date' TEXT NOT NULL,
  'usage_date' TEXT DEFAULT NULL,
  'recipient_id' INTEGER NOT NULL,
  'special_offer_id' INTEGER NOT NULL,
  FOREIGN KEY ('recipient_id') REFERENCES 'recipients' ('id') ON DELETE CASCADE ON UPDATE NO ACTION,
  FOREIGN KEY ('special_offer_id') REFERENCES 'special_offers' ('id') ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE INDEX 'expiration_date_index' ON "vouchers" ("expiration_date");
CREATE INDEX 'usage_date_index' ON "vouchers" ("usage_date");
CREATE INDEX 'recipient_id_index' ON "vouchers" ("recipient_id");
CREATE INDEX 'special_offer_id_index' ON "vouchers" ("special_offer_id");