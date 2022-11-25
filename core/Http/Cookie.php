<?php

namespace Core\Http;

/**
 * Represents a cookie.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Cookie
{
    const SAMESITE_NONE = 'none';
    const SAMESITE_LAX = 'lax';
    const SAMESITE_STRICT = 'strict';

    protected $name;
    protected $value;
    protected $domain;
    protected $expire;
    protected $path;
    protected $secure;
    protected $httpOnly;

    private $raw;
    private $sameSite = null;
    private $secureDefault = false;

    const RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
    const RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
    const RESERVED_CHARS_TO = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];

    public static function create($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = self::SAMESITE_LAX)
    {
        return new self($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * @param string                        $name     The name of the cookie
     * @param string|null                   $value    The value of the cookie
     * @param int|string|\DateTimeInterface $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param string|null                   $domain   The domain that the cookie is available to
     * @param bool|null                     $secure   Whether the client should send back the cookie only over HTTPS or null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     * @param string|null                   $sameSite Whether the cookie will be available for cross-site requests
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = 'lax')
    {
        // from PHP source code
        if ($raw && false !== strpbrk($name, self::RESERVED_CHARS_LIST)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = self::expiresTimestamp($expire);
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw = $raw;
        $this->sameSite = $this->withSameSite($sameSite)->sameSite;
    }

    /**
     * Creates a cookie copy with a new value.
     */
    public function withValue($value)
    {
        $cookie = clone $this;
        $cookie->value = $value;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new domain that the cookie is available to.
     */
    public function withDomain($domain)
    {
        $cookie = clone $this;
        $cookie->domain = $domain;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new time the cookie expires.
     */
    public function withExpires($expire = 0)
    {
        $cookie = clone $this;
        $cookie->expire = self::expiresTimestamp($expire);

        return $cookie;
    }

    /**
     * Converts expires formats to a unix timestamp.
     */
    private static function expiresTimestamp($expire = 0)
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        return 0 < $expire ? (int) $expire : 0;
    }

    /**
     * Creates a cookie copy with a new path on the server in which the cookie will be available on.
     */
    public function withPath($path)
    {
        $cookie = clone $this;
        $cookie->path = '' === $path ? '/' : $path;

        return $cookie;
    }

    /**
     * Creates a cookie copy that only be transmitted over a secure HTTPS connection from the client.
     */
    public function withSecure($secure = true)
    {
        $cookie = clone $this;
        $cookie->secure = $secure;

        return $cookie;
    }

    /**
     * Creates a cookie copy that be accessible only through the HTTP protocol.
     */
    public function withHttpOnly($httpOnly = true)
    {
        $cookie = clone $this;
        $cookie->httpOnly = $httpOnly;

        return $cookie;
    }

    /**
     * Creates a cookie copy that uses no url encoding.
     */
    public function withRaw($raw = true)
    {
        if ($raw && false !== strpbrk($this->name, self::RESERVED_CHARS_LIST)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $this->name));
        }

        $cookie = clone $this;
        $cookie->raw = $raw;

        return $cookie;
    }

    /**
     * Creates a cookie copy with SameSite attribute.
     */
    public function withSameSite($sameSite)
    {
        if ('' === $sameSite) {
            $sameSite = null;
        } elseif (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }

        if (!\in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $cookie = clone $this;
        $cookie->sameSite = $sameSite;

        return $cookie;
    }

    /**
     * Returns the cookie as a string.
     */
    public function __toString()
    {
        if ($this->isRaw()) {
            $str = $this->getName();
        } else {
            $str = str_replace(self::RESERVED_CHARS_FROM, self::RESERVED_CHARS_TO, $this->getName());
        }

        $str .= '=';

        if ('' === (string) $this->getValue()) {
            $str .= 'deleted; expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            if (0 !== $this->getExpiresTime()) {
                $str .= '; expires='.gmdate('D, d-M-Y H:i:s T', $this->getExpiresTime()).'; Max-Age='.$this->getMaxAge();
            }
        }

        if ($this->getPath()) {
            $str .= '; path='.$this->getPath();
        }

        if ($this->getDomain()) {
            $str .= '; domain='.$this->getDomain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        if (null !== $this->getSameSite()) {
            $str .= '; samesite='.$this->getSameSite();
        }

        return $str;
    }

    /**
     * Gets the name of the cookie.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the domain that the cookie is available to.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     */
    public function getExpiresTime()
    {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     */
    public function getMaxAge()
    {
        $maxAge = $this->expire - time();

        return 0 >= $maxAge ? 0 : $maxAge;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     */
    public function isSecure()
    {
        return $this->secure ?: $this->secureDefault;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared.
     */
    public function isCleared()
    {
        return 0 !== $this->expire && $this->expire < time();
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     */
    public function isRaw()
    {
        return $this->raw;
    }

    /**
     * Gets the SameSite attribute.
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * @param $default The default value of the "secure" flag when it is set to null
     */
    public function setSecureDefault($default)
    {
        $this->secureDefault = $default;
    }
}
