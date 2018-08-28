<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Recipient
 *
 * @package VoucherPool\Model
 */
class Recipient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email'];

    /**
     * No need to keep creation and update timestamp
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines the one-to-many relationship between Recipient and Vouchers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vouchers()
    {
        return $this->hasMany('VoucherPool\\Model\\Voucher');
    }

    /**
     * @param $query
     * @param string $search Search term
     * @return mixed
     */
    public function scopeSearch($query, $search)
    {
        if($search) {
            return $query->where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%");
        }

        return $query;
    }

    /**
     * Validate name value
     *
     * @param string $value
     *
     * @throws \InvalidArgumentException if the name is not valid
     */
    public function setNameAttribute($value)
    {
        if ($value == '') {
            throw new \LengthException("Recipient name can not be empty!", 400);
        }

        if (strlen($value) > 255) {
            throw new \LengthException("Recipient name can not be longer than 255 characters!", 400);
        }

        if (preg_match('/[^a-zA-Z0-9 ]/', $value)) {
            throw new \InvalidArgumentException("Recipient name can only contain letters, numbers and space character!", 400);
        }

        $this->attributes['name'] = $value;
    }

    /**
     * Validate E-mail value
     *
     * @param string $value
     *
     * @throws \InvalidArgumentException if the email is not valid
     */
    public function setEmailAttribute($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException("Recipient e-mail address is not valid!", 400);
        }

        $this->attributes['email'] = $value;
    }
}