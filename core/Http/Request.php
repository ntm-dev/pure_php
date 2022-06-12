<?php

namespace Core\Http;

use ArrayObject;
use Core\Http\ServerBag;
use Core\Pattern\Singleton;

class Request
{
    use Singleton;

    /**
     * Request body parameters ($_POST).
     *
     * @var ArrayObject
     */
    private $parameters;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @var ServerBag
     */
    public $server;

    /** 
     * Request header.
     *
     * @var  array
     */
    public $headers = [];

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        $this->server = new ServerBag;
        $this->headers = $this->server->getHeaders();
        $this->parameters = new ArrayObject($_REQUEST, ArrayObject::ARRAY_AS_PROPS);
    }

    public function method()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    public function getPathInfo(): string
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): string
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return $this->getSchemeAndHttpHost();
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return $this->getSchemeAndHttpHost() . $this->getPathInfo();
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        $query = $this->getQueryString();

        return $query ? $this->url() . "/?" . $query : $this->url();
    }

    /**
     * Get the current decoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Get a segment from the URI (1 based index).
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function segment(int $index, $default = null)
    {
        $segments = $this->segments();
        if (!isset($segments[$index - 1])) {
            return $default;
        }

        return $segments[$index - 1];
    }

    /**
     * Get all of the segments for the request path.
     *
     * @return array
     */
    public function segments()
    {
        $segments = explode('/', $this->decodedPath());

        return array_values(array_filter($segments, function ($value) {
            return $value !== '';
        }));
    }

    /**
     * Gets the scheme and HTTP host.
     */
    public function getSchemeAndHttpHost(): string
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Gets the request's scheme.
     */
    public function getScheme(): string
    {
        return $this->secure() ? 'https' : 'http';
    }

    public function secure(): bool
    {
        $https = $this->server->get('HTTPS');

        return !empty($https) && 'off' !== strtolower($https);
    }

    /**
     * Get the client IP address.
     *
     * @return string|null
     */
    public function ip()
    {
        return $this->server->get('REMOTE_ADDR');
    }

    /**
     * Get the client user agent.
     *
     * @return string|null
     */
    public function userAgent()
    {
        return $this->headers['User-Agent'];
    }

    /**
     * Instead, you may use the "input" method.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->parameters->{$key})) {
            return $this->parameters->{$key};
        }

        return $default;
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     */
    public function getHttpHost(): string
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    public function getHost(): string
    {
        if (!$host = $this->headers['HOST']) {
            if (!$host = $this->server->get('SERVER_NAME')) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        return $host;
    }

    public function getPort(): string
    {
        if ($this->headers['HOST']) {
            return $this->server->get('SERVER_PORT');
        }

        return 'https' === $this->getScheme() ? 443 : 80;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of a PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers['X-PJAX'] == true;
    }


    /**
     * Returns true if the request is an XMLHttpRequest.
     *
     */
    public function isXmlHttpRequest(): bool
    {
        return 'XMLHttpRequest' == $this->headers['X-Requested-With'];
    }

    /**
     * Generates the normalized query string for the Request.
     */
    public function getQueryString(): ?string
    {
        return $this->server->get('QUERY_STRING');
    }

    /**
     * Prepares the path info.
     */
    protected function preparePathInfo(): string
    {
        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        // Remove the query string from REQUEST_URI
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        return $requestUri;
    }

    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }

        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }
}
