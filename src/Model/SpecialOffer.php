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
 * SpecialOffer
 *
 * @package VoucherPool\Model
 */
class SpecialOffer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'discount'];

    /**
     * No need to keep creation and update timestamp
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Defines the one-to-many relationship between Special Offer and Vouchers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vouchers()
    {
        return $this->hasMany('VoucherPool\\Model\\Voucher');
    }

    public function scopeSearch($query, $search)
    {
        if($search) {
            return $query->where('name', 'LIKE', "%$search%");
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
            throw new \LengthException("Special Offer name can not be empty!", 400);
        }

        if (strlen($value) > 255) {
            throw new \LengthException("Special Offer name can not be longer than 255 characters!", 400);
        }

        $this->attributes['name'] = $value;
    }

    /**
     * Validate discount value
     *
     * @param string|float $value
     *
     * @throws \InvalidArgumentException if the discount is not valid
     */
    public function setDiscountAttribute($value)
    {
        if ($value == '') {
            throw new \InvalidArgumentException("Discount can not be empty!", 400);
        }

        if(is_numeric($value) === false) {
            throw new \InvalidArgumentException("Discount MUST be numeric!", 400);
        }

        $valueFloat = round($value, 2);

        if ($valueFloat < 0 || $valueFloat > 1) {
            throw new \OutOfRangeException("Discount must be between 0 and 1!", 400);
        }

        $this->attributes['discount'] = $valueFloat;
    }
}