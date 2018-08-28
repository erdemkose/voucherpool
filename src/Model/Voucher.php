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
 * Voucher
 *
 * @property mixed expiration_date
 * @property string code
 * @property mixed usage_date
 * @package VoucherPool\Model
 */
class Voucher extends Model
{
    /**
     * Alphabet to use when generating voucher code
     * Visually similar letters and digits are omitted
     */
    private const CODE_ALPHABET = "23456789ABCDEFGHJKLMNPRSTUVWXYZ";

    /**
     * Voucher code length
     */
    private const CODE_LENGTH = 8;

    /**
     * When serializing this object, generate and append these attributes
     * @links https://laravel.com/docs/5.6/eloquent-serialization
     *
     * @var array
     */
    protected $appends = ['is_expired', 'is_used', 'recipient', 'special_offer'];

    /**
     * The attributes that are mass assignable.
     * @links https://laravel.com/docs/5.6/eloquent
     *
     * @var array
     */
    protected $fillable = ['expiration_date', 'recipient_id', 'special_offer_id'];

    /**
     * Stop Eloquent to track created/updated timestamps
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the Recipient of this Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo('VoucherPool\\Model\\Recipient');
    }

    /**
     * Get the Special Offer of this Voucher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function specialOffer()
    {
        return $this->belongsTo('VoucherPool\\Model\\SpecialOffer');
    }

    /**
     * Scope function to get only the valid vouchers
     *
     * A valid voucher means "not used" and "not expired".
     *
     * @param $query
     * @return mixed
     */
    public function scopeValid($query)
    {
        return $query->notUsed()->notExpired();
    }

    /**
     * Scope function to get the unused vouchers
     *
     * @param $query
     * @return mixed
     */
    public function scopeNotUsed($query)
    {
        return $query->whereNull('usage_date');
    }

    /**
     * Scope function to get the vouchers that expiration date is in the future
     *
     * @param $query
     * @return mixed
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expiration_date', '>=', date('Y-m-d'));
    }

    /**
     * Scope function to get the vouchers that are expiring today
     *
     * @param $query
     * @return mixed
     */
    public function scopeExpiresToday($query)
    {
        return $query->notUsed()->where('expiration_date', date('Y-m-d'));
    }

    /**
     * Scope function to get the vouchers that are expired but not used
     *
     * @param $query
     * @return mixed
     */
    public function scopeExpiredNotUsed($query)
    {
        return $query->notUsed()->where('expiration_date', '<', date('Y-m-d'));
    }

    /**
     * Scope function to search the vouchers by code.
     * If the search query is empty, no search is performed.
     *
     * @param $query
     * @param $search
     * @return mixed
     */
    public function scopeSearch($query, $search)
    {
        if($search) {
            return $query->where('code', 'LIKE', "%$search%");
        }

        return $query;
    }

    /**
     * Getter for is_expired attribute
     *
     * @return bool
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiration_date < date('Y-m-d');
    }

    /**
     * Getter for is_used attribute
     *
     * @return bool
     */
    public function getIsUsedAttribute()
    {
        return $this->usage_date != null;
    }

    /**
     * Getter for recipient attribute
     *
     * @return Model
     */
    public function getRecipientAttribute()
    {
        return $this->recipient()->first();
    }

    /**
     * Getter for special_offer attribute
     *
     * @return Model
     */
    public function getSpecialOfferAttribute()
    {
        return $this->specialOffer()->first();
    }

    /**
     * Setter for expiration_date attribute
     *
     * Checks if the $value is a valid date
     *
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function setExpirationDateAttribute($value)
    {
        $expiration_date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($expiration_date === false) {
            throw new \InvalidArgumentException("Voucher expiration date is in wrong format!", 400);
        }

        if ($expiration_date->format('Y-m-d') != $value) {
            throw new \InvalidArgumentException("Voucher expiration date is not valid!", 400);
        }

        $this->attributes['expiration_date'] = $value;
    }

    /**
     * Setter for usage_date attribute
     *
     * Checks if the $value is a valid date
     *
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function setUsageDateAttribute($value)
    {
        $usage_date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($usage_date === false) {
            throw new \InvalidArgumentException("Voucher usage date is in wrong format!", 400);
        }

        if ($usage_date->format('Y-m-d') != $value) {
            throw new \InvalidArgumentException("Voucher usage date is not valid!", 400);
        }

        $this->attributes['usage_date'] = $value;
    }

    /**
     * Setter for special_offer_id attribute
     *
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function setSpecialOfferIdAttribute($value)
    {
        if(is_numeric($value) === false) {
            throw new \InvalidArgumentException("Voucher special offer id MUST be an integer!", 400);
        }

        $special_offer = SpecialOffer::find($value);
        if($special_offer == null) {
            throw new \RuntimeException("Could not find the special offer!", 404);
        }

        $this->attributes['special_offer_id'] = $value;
    }

    /**
     * Setter for recipient_id attribute
     *
     * @param $value
     * @throws \InvalidArgumentException
     */
    public function setRecipientIdAttribute($value)
    {
        if(is_numeric($value) === false) {
            throw new \InvalidArgumentException("Voucher recipient id MUST be an integer!", 400);
        }

        $recipient = Recipient::find($value);
        if($recipient == null) {
            throw new \RuntimeException("Could not find the recipient!", 404);
        }

        $this->attributes['recipient_id'] = $value;
    }

    /**
     * Generate random voucher code using the characters in CODE_ALPHABET
     * This function DOES NOT check if the generated code is unique or not.
     *
     * Code length can be changed by modifying CODE_LENGTH constant.
     *
     * @return string
     */
    public static function generateRandomCode()
    {
        $alphabetlength = strlen(self::CODE_ALPHABET);

        $code = "";
        for($i=0; $i < self::CODE_LENGTH; $i++) {
            $random = rand(0, $alphabetlength - 1);
            $code .= substr(self::CODE_ALPHABET, $random, 1);
        }

        return $code;
    }
}