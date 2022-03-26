<?php

namespace Support\Validation;

use Support\Helper\Str;

/**
 * Define rule for validation.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Rule
{
    /** @var \ArrayObject requests*/
    protected static $requests;

    /**
     * Validate that a required attribute exists.
     *
     * @param  mixed  $value
     */
    public function validateRequired($value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) &&  '' === Str::trim($value)) {
            return false;
        } elseif ((is_array($value) || $value instanceof \Countable) && count($value) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Validate that the field under validation must be present
     * and not empty if the another field is equal to any value.
     *
     * @param  mixed  $value
     * @param  array  $parameters [filed_name, $value]
     */
    public function validateRequiredIf($value, $parameters)
    {
        $parameters = is_string($parameters) ? [$parameters] : $parameters;
        $requireField = array_shift($parameters);

        if ( !$this->validateRequired(static::$requests->{$requireField}) ) {
            return true;
        }

        if ( !in_array(static::$requests->{$requireField}, $parameters) ) {
            return true;
        }

        return $this->validateRequired($value);
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateAlpha($value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateAlphaDash($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateAlphaNum($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateArray($value)
    {
        if (! is_array($value)) {
            return false;
        }

        return empty($value);
    }

    /**
     * Validate that an attribute is a boolean.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateBoolean($value)
    {
        $acceptable = [true, false, 0, 1, '0', '1'];

        return in_array($value, $acceptable, true);
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateDate($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        if ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param  mixed  $value
     * @param  array!string  $parameters
     * @return bool
     */
    public function validateDateFormat($value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $parameters = is_string($parameters) ? [$parameters] : $parameters;

        foreach ($parameters as $format) {
            $date = \DateTime::createFromFormat('!'.$format, $value);

            if ($date && $date->format($format) == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that an attribute matches a numberic.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @return void
     */
    protected function validateNumeric($value)
    {
        if (is_numeric($value)) {
            return true;
        }

        return false;
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateEmail($value)
    {
        return !!filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate that an attribute is a valid Hankakus characters(Half-width kana).
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateHankaku($value)
    {
        return !!preg_match('/^[\p{Han}]+$/u', $value);
    }

    /**
     * Validate that an attribute is a valid Katakana characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateKatakana($value)
    {
        return !!preg_match('/^[\p{Katakana}]+$/u', $value);
    }

    /**
     * Validate that an attribute is a valid Hiragana characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateHiragana($value)
    {
        return !!preg_match('/^[\p{Hiragana}]+$/u', $value);
    }

    /**
     * Validate that an attribute is a valid Furigana characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateFurigana($value)
    {
        return !!preg_match('/^[\p{Katakana}\p{Hiragana}]+$/u', $value);
    }

    /**
     * Validate that an attribute is a valid Japanese characters.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function validateJapanese($value)
    {
        return !!preg_match('/^[\p{Katakana}\p{Hiragana}\p{Han}]+$/u', $value);
    }

    /**
     * Validate that an attribute is a valid url.
     * 
     * @see http://www.faqs.org/rfcs/rfc2396)
     * 
     * Beware a valid URL may not specify the HTTP protocol http:// so further
     * validation may be required to determine the URL uses an expected protocol,
     * e.g. ssh:// or mailto:. Note that the function will only find ASCII URLs to be valid;
     * internationalized domain names (containing non-ASCII characters) will fail.
     * 
     * @param  mixed  $value
     * @return bool
     */
    public function validateUrl($value)
    {
        return !!filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Validate that an attribute as IP address,
     * optionally only IPv4 or IPv6 or not from private or reserved ranges.
     * 
     * @param  mixed  $value
     * @return bool
     */
    public function validateIp($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP);
    }

    /**
     * Validate that an attribute has a minimum length.
     *
     * @param  mixed $value
     * @param  int   $target
     * @return bool
     */
    public function validateMin($value, $target)
    {
        if (is_array($value)) {
            return count($value) >= $target;
        }
        if (is_numeric($value)) {
            return $value >= $target;
        }
        if (is_string($value)) {
            return Str::length($value) >= $target;
        }

        return false;
    }

    /**
    * Validate that an attribute has a minimum length.
    *
    * @param  mixed $value
    * @param  int   $target
    * @return bool
    */
    public function validateMinLength($value, $target)
    {
        if (is_array($value)) {
            return count($value) >= $target;
        }
        if (is_numeric($value) || is_string($value)) {
            return Str::length($value) >= $target;
        }

        return false;
    }

    /**
     * Validate that an attribute has a maximum length.
     *
     * @param  mixed $value
     * @param  int   $target
     * @return bool
     */
    public function validateMax($value, $target)
    {
        if (is_array($value)) {
            return count($value) <= $target;
        }
        if (is_numeric($value)) {
            return $value <= $target;
        }
        if (is_string($value)) {
            return Str::length($value) <= $target;
        }

        return false;
    }

    /**
     * Validate that an attribute has a maximum length.
     *
     * @param  mixed $value
     * @param  int   $target
     * @return bool
     */
    public function validateMaxLength($value, $target)
    {
        if (is_array($value)) {
            return count($value) <= $target;
        }
        if (is_numeric($value) || is_string($value)) {
            return Str::length($value) <= $target;
        }

        return false;
    }

    /**
     * Validate that an attribute has a specified length.
     *
     * @param  mixed $value
     * @param  int   $target
     * @return bool
     */
    public function validateLength($value, $target)
    {
        if (is_array($value)) {
            return count($value) == $target;
        }
        if (is_numeric($value)) {
            return $value == $target;
        }
        if (is_string($value)) {
            return Str::length($value) == $target;
        }

        return false;
    }

    /**
     * Validate the field under validation must be included in the given list of values.
     *
     * @param  mixed $value
     * @param  array $target
     * @return bool
     */
    public function validateIn($value, $target)
    {
        if (!is_array($target)) {
            return false;
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!in_array($v, $target)) {
                    return false;
                }
            }
            return true;
        }
        if (is_string($value)) {
            return in_array($value, $target);
        }

        return false;
    }

    /**
     * Validate the field under validation must be a valid JSON string.
     *
     * @param  mixed $value
     * @return bool
     */
    public function validateJson($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return is_null(json_decode($value));
    }
}
