<?php

namespace Core\Http;

trait ResponseTrait
{
    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * Get the status code for the response.
     *
     * @return int
     */
    public function status()
    {
        return $this->getStatusCode();
    }

    /**
     * Get the status text for the response.
     *
     * @return string
     */
    public function statusText()
    {
        return $this->statusText;
    }

    /**
     * Get the content of the response.
     *
     * @return string
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        $original = $this->original;

        return $original instanceof self ? $original->{__FUNCTION__}() : $original;
    }

    /**
     * Set a header on the Response.
     *
     * @param  string  $key
     * @param  array|string  $values
     * @param  bool  $replace
     * @return $this
     */
    public function header($key, $values, $replace = true)
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  array  $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param  string  $name
     * @return $this
     */
    public function setCookie(string $name, string $value = "", int $minutes = 0, string $path = "", string $domain = "", bool $secure = false, bool $httpOnly = false)
    {
        $this->cookies[$name] = [
            'name'     => $name,
            'value'    => $value,
            'minutes'  => $minutes,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httpOnly' => $httpOnly
        ];

        return $this;
    }

    /**
     * Expire a cookie when sending the response.
     *
     * @param  string  $name
     * @param  string|null  $path
     * @param  string|null  $domain
     * @return $this
     */
    public function withoutCookie($name, $path = "", $domain = "")
    {
        $this->setCookie($name, "", -2628000, $path, $domain);

        return $this;
    }

    private function cookieToString($cookie)
    {
        $str = '';

        if ('' === (string) $cookie['name']) {
            $str .= 'deleted; expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $str = "{$cookie['name']}={$cookie['value']}";
            $getExpiresTime = strtotime("now + 30 days");
            $maxAge = $getExpiresTime - time();
            $maxAge = 0 >= $maxAge ? 0 : $maxAge;
            $str .= '; expires='.gmdate('D, d-M-Y H:i:s T', $getExpiresTime).'; Max-Age='.$maxAge;
        }

        if ($cookie['path']) {
            $str .= '; path='.$cookie['path'];
        }

        if ($cookie['domain']) {
            $str .= '; domain='.$cookie['domain'];
        }

        if ($cookie['httpOnly']) {
            $str .= '; secure';
        }

        if ($cookie['httpOnly']) {
            $str .= '; httponly';
        }

        return $str;
    }
}
