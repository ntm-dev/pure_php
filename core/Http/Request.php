<?php

namespace Core\Http;

use ArrayObject;
use Core\Http\ServerBag;
use Core\Pattern\Singleton;
use Core\Support\Helper\Str;
use Core\Support\Helper\Arr;

class Request
{
    use Singleton;

    /**
     * Request body parameters ($_REQUEST).
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

    /** 
     * Request session.
     *
     * @var  \Core\Http\Section
     */
    public $session;

    public function __construct()
    {
        $this->server = new ServerBag;
        $this->session = Session::getInstance();
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

        return $query ? trim($this->url(), "/") . "/?" . $query : $this->url();
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
        if (isset($this->headers['X_FORWARDED_PROTO'])) {
           return $this->headers['X_FORWARDED_PROTO'] == "https";
        }
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
        if ($this->server->has('HTTP_CLIENT_IP')) { // ip is from the share internet
            return $this->server->get('HTTP_CLIENT_IP');
        } elseif ($this->server->has('HTTP_CF_CONNECTING_IP')) { // ip behind CloudFlare network
            return $this->server->get('HTTP_CF_CONNECTING_IP');
        } elseif ($this->server->has('HTTP_X_FORWARDED_FOR')) { // ip is from the proxy
            return $this->server->get('HTTP_X_FORWARDED_FOR');
        }

        return $this->server->get('REMOTE_ADDR');
    }

    /**
     * Get the client user agent.
     *
     * @return string|null
     */
    public function userAgent()
    {
        return $this->header('User-Agent');
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
     * Get the JSON payload for the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $json = (array) json_decode(file_get_contents('php://input'), true);
        }

        if ($key) {
            return Arr::get($json, $key, $default);
        }

        return $json;
    }


    /**
     * Get input from request without some arguments
     *
     * @param  array  $arguments
     * @return ArrayObject
     */
    public function except($arguments)
    {
        $parameters = $this->parameters->getArrayCopy();
        foreach ($arguments as $argument) {
            if (isset($parameters[$argument])) {
                unset($parameters[$argument]);
            }
        }

        return $parameters;
    }

    /**
     * Gets input from request consisting of specified arguments
     *
     * @param  array  $arguments
     * @return ArrayObject
     */
    public function only($arguments)
    {
        $parameters = [];
        foreach ($arguments as $argument) {
            if (isset($this->parameters[$argument])) {
                $parameters[$argument] = $this->parameters[$argument];
            }
        }

        return $parameters;
    }

    /**
     * Get an input from request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * Get all input from request.
     *
     * @return mixed
     */
    public function all()
    {
        return $this->parameters->getArrayCopy();
    }

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    public function getHost()
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

    public function getPort()
    {
        if ($this->headers['HOST']) {
            if (isset($this->headers['X_FORWARDED_PORT'])) {
                return $this->headers['X_FORWARDED_PORT'];
            }
            return Str::startsWith($this->headers['HOST'], 'localhost')
                ? Str::after($this->headers['HOST'], ":")
                : $this->server->get('SERVER_PORT');
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
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->header('X-Requested-With');
    }

    /**
     * Generates the normalized query string for the Request.
     */
    public function getQueryString()
    {
        return $this->server->get('QUERY_STRING');
    }

    /**
     * Prepares the path info.
     */
    protected function preparePathInfo()
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
            $this->server->remove('ORIG_PATH_INFO');
        }
        if ('' != $this->server->get('QUERY_STRING') && !preg_match("/\?/", $requestUri)) {
            $requestUri .= '?'.$this->server->get('QUERY_STRING');
        }

        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    /**
     * Get request header
     *
     * @param  string  $key
     * @return mixed
     */
    public function header($key = '')
    {
        if ($key) {
            return $this->server->getHeader(strtoupper(Str::snake(Str::headline($key))));
        }

        return $this->headers;
    }

    /**
     * Check isset key in request
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return !!$this->parameters->offsetExists($key);
    }

    /**
     * Triggered when this class is treated like a string.
     *
     * @return string
     */
    public function __toString()
    {
        return serialize($this);
    }
}
