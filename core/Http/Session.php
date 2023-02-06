<?php

namespace Core\Http;

use Core\Config;
use Core\Pattern\Singleton;
use Core\Support\Helper\Arr;
use Core\Support\Helper\Str;
use Core\Support\Facades\Date;
use Core\Support\Facades\Response;

/**
 * Session class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Session
{
    use Singleton;

    /**
     * The session ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The session attributes.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Session store started status.
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Create a new session instance.
     *
     * @param  string|null  $id
     * @return void
     */
    public function __construct($id = null)
    {
        if (!\extension_loaded('session')) {
            throw new \LogicException('PHP extension "session" is required.');
        }
        $this->setId($id ?: $this->getIdFromCookie());
        $this->start();
        $this->ageFlashData();
    }

    public function getConfig($key = '')
    {
        if ($key) {
            return Config::get("session.{$key}");
        }

        return Config::get('session');
    }

    /**
     * Start the session, reading the data from a handler.
     *
     * @return bool
     */
    public function start()
    {
        $this->setPhpSessionId($this->id);
        if (!session_start()) {
            throw new \RuntimeException('Failed to start the session.');
        }

        $this->loadSession();

        return $this->started = true;
    }

    /**
     * Load the session data from the handler.
     *
     * @return void
     */
    protected function loadSession()
    {
        $this->attributes = &$_SESSION;

        if ($this->expired()) {
            $this->flush();
        }
        $this->put('id', $this->id);

        return $this->attributes;
    }

    /**
     * Check session expiration.
     *
     * @return bool
     */
    public function expired()
    {
        $time = time();

        if (! $this->get('__LAST_ACTIVITY__', false)) {
            $this->put('__LAST_ACTIVITY__', $time);
        }

        return $time - $this->get('__LAST_ACTIVITY__', $time) > $this->getConfig('lifetime') * 60;
    }

    /**
     * Prepare the raw string data from the session for unserialization.
     *
     * @param  string  $data
     * @return string
     */
    protected function prepareForUnserialize($data)
    {
        return $data;
    }

    /**
     * Age the flash data for the session.
     *
     * @return void
     */
    public function ageFlashData()
    {
        $this->forget($this->get('_flash.old', []));

        $this->put('_flash.old', $this->get('_flash.new', []));

        $this->put('_flash.new', []);
    }

    /**
     * Get all of the session data.
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * Get a subset of the session data.
     *
     * @param  array  $keys
     * @return array
     */
    public function only(array $keys)
    {
        return Arr::only($this->attributes, $keys);
    }

    /**
     * Checks if a key exists.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key)
    {
        return Arr::exists($this->attributes, $key);
    }

    /**
     * Determine if the given key is missing from the session data.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function missing($key)
    {
        return ! $this->exists($key);
    }

    /**
     * Checks if a key is present and not null.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key)
    {
        return $this->exists($key) && $this->get($key, false);
    }

    /**
     * Get an item from the session.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * Get the value of a given key and then forget it.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    /**
     * Determine if the session contains old input.
     *
     * @param  string|null  $key
     * @return bool
     */
    public function hasOldInput($key = null)
    {
        $old = $this->getOldInput($key);

        return is_null($key) ? count($old) > 0 : ! is_null($old);
    }

    /**
     * Get the requested item from the flashed input array.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getOldInput($key = null, $default = null)
    {
        return Arr::get($this->get('_old_input', []), $key, $default);
    }

    /**
     * Replace the given session attributes entirely.
     *
     * @param  array  $attributes
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            Arr::set($this->attributes, $arrayKey, $arrayValue);
        }
    }

    /**
     * Push a value onto a session array.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Increment the value of an item in the session.
     *
     * @param  string  $key
     * @param  int  $amount
     * @return mixed
     */
    public function increment($key, $amount = 1)
    {
        $this->put($key, $value = $this->get($key, 0) + $amount);

        return $value;
    }

    /**
     * Decrement the value of an item in the session.
     *
     * @param  string  $key
     * @param  int  $amount
     * @return int
     */
    public function decrement($key, $amount = 1)
    {
        return $this->increment($key, $amount * -1);
    }

    /**
     * Flash a key / value pair to the session.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function flash($key, $value = true)
    {
        $this->put($key, $value);

        $this->push('_flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * Flash a key / value pair to the session for immediate use.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function now($key, $value)
    {
        $this->put($key, $value);

        $this->push('_flash.old', $key);
    }

    /**
     * Reflash all of the session flash data.
     *
     * @return void
     */
    public function reflash()
    {
        $this->mergeNewFlashes($this->get('_flash.old', []));

        $this->put('_flash.old', []);
    }

    /**
     * Reflash a subset of the current flash data.
     *
     * @param  array|mixed  $keys
     * @return void
     */
    public function keep($keys = null)
    {
        $this->mergeNewFlashes($keys = is_array($keys) ? $keys : func_get_args());

        $this->removeFromOldFlashData($keys);
    }

    /**
     * Merge new flash keys into the new flash array.
     *
     * @param  array  $keys
     * @return void
     */
    protected function mergeNewFlashes(array $keys)
    {
        $values = array_unique(array_merge($this->get('_flash.new', []), $keys));

        $this->put('_flash.new', $values);
    }

    /**
     * Remove the given keys from the old flash data.
     *
     * @param  array  $keys
     * @return void
     */
    protected function removeFromOldFlashData(array $keys)
    {
        $this->put('_flash.old', array_diff($this->get('_flash.old', []), $keys));
    }

    /**
     * Flash an input array to the session.
     *
     * @param  array  $value
     * @return void
     */
    public function flashInput(array $value)
    {
        $this->flash('_old_input', $value);
    }

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string  $key
     * @return mixed
     */
    public function remove($key)
    {
        return Arr::pull($this->attributes, $key);
    }

    /**
     * Remove one or many items from the session.
     *
     * @param  string|array  $keys
     * @return void
     */
    public function forget($keys)
    {
        Arr::forget($this->attributes, $keys);
    }

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function flush()
    {
        $this->attributes = [];
    }

    /**
     * Determine if the session has been started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }
    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get id from Cookie.
     *
     * @return string|null
     */
    public function getIdFromCookie()
    {
        $name = $this->getName();

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * Set the session ID.
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the ID of an active session.');
        }

        $this->id = $this->isValidId($id) ? $id : $this->generateSessionId();
    }

    /**
     * Set php session id.
     *
     * @param  string  $id
     * @return string|false
     */
    public function setPhpSessionId($id)
    {
        return session_id($id);
    }

    /**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    public function isValidId($id)
    {
        return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
    }

    /**
     * Has a session started?
     */
    public function isActive()
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    protected function generateSessionId()
    {
        return Str::random(40);
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        return $this->getConfig('expire_on_close') ? 0 : Date::now()->addMinutes($this->getConfig('lifetime'))->timestamp();
    }

    /**
     * Add the session cookie to the application response.
     *
     * @return void
     */
    protected function addCookieToResponse()
    {
        Response::setCookie(
            $this->getName(), $this->getId(), $this->getCookieExpirationDate(),
            $this->getConfig('path') ?: '', $this->getConfig('domain') ?: '', $this->getConfig('secure') ?: false,
            $this->getConfig('http_only'), false, $this->getConfig('same_site')
        );
    }

    /**
     * Get defined session name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getConfig('cookie');
    }

    public function __destruct()
    {
        $this->addCookieToResponse();
    }
}
