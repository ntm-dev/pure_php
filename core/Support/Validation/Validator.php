<?php

namespace Core\Support\Validation;

use RuntimeException;
use Core\Support\Helper\Str;
use Core\Support\Validation\Rule;
use Core\Support\Validation\ValidatorInterface;
use Core\Support\Traits\CallStaticAble;

/**
 * Validator class.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
class Validator implements ValidatorInterface
{
    /** Alias method prefix */
    protected const ALIAS_METHOD_PREFIX = ['get', 'exec'];

    /** @var \ArrayObject requests*/
    protected static $requests;

    /** @var array errors*/
    protected static $errors = [];

    /** @var array rule for validation */
    protected static $rules;

    /** @var array messages for defined rule */
    protected static $messages;

    /** @var static its own instance */
    private static $instance;

    /** @var string stop validate key */
    const BAIL_KEY = 'bail';

    /**
     * Create a new validator.
     *
     * @param  array $data The data for validate
     * @return void
     */
    public function __construct($data = [])
    {
        $data = empty($data) ? $_REQUEST : $data;
        self::$requests = collect($data);
    }

    /**
     * Execute validate data.
     * 
     * @return bool
     */
    public function execValidate()
    {
        self::$rules = $this->rules();
        self::$messages = $this->messages();
        $this->splitRules();
        foreach (self::$rules as $attribute => $attrRules) {
            $this->validateRules($attribute, $attrRules);
        }

        return !(bool)self::$errors;
    }

    /**
     * Validate for each attribute.
     * 
     * @param  string $attribute
     * @param  array  $rules
     * @return void
     */
    private function validateRules($attribute, $rules)
    {
        $attributeValue = $this->getAttributeValue($attribute);
        $stopOnFail = $this->isStopOnFail($rules);

        foreach ($rules as $rule) {
            $result = true;
            if ($rule instanceof \Closure) {
                $fail = function ($message) use ($attribute, $result) {
                    $$result = false;
                    return $this->setErrorMessage($attribute, $message);
                };
                $rule($attribute, $attributeValue, $fail);
            } else {
                $callable = $this->extractMethod($rule);
                $result = $this->execDefinedRule($attributeValue, $callable);
                if (!$result) {
                    $this->setError($attribute, $callable['method']);
                } elseif (($callable['method'] == "requiredIf") && !$this->execDefinedRule($attributeValue, $callable)) {
                    break;
                }
            }
            if (!$result && $stopOnFail) {
                break;
            }
        }
    }

    /**
     * Exectute defined rule
     *
     * @param mixed $attributeValue
     * @param array $callable [method => target value]
     */
    private function execDefinedRule($attributeValue, $callable)
    {
        if (method_exists(Rule::class, $callable['method']) && (new \ReflectionMethod(Rule::class, $callable['method']))->isPublic()) {
            return call_user_func_array([new Rule, $callable['method']], [$attributeValue, $callable['target']]);
        }

        throw new \BadMethodCallException("Method [{$callable['method']}] does not exist or is not accessible in " . Rule::class);
    }

    /**
     * Check validation is stop on fail or not.
     * 
     * @param  array &$rule
     * @return bool
     */
    private function isStopOnFail(&$rules)
    {
        $stopOnFail = false;
        if ($rules[0] == self::BAIL_KEY) {
            $stopOnFail = true;
            unset($rules[0]);
        }

        return $stopOnFail;
    }

    /**
     * Extract method and target value.
     * 
     * @param  string $rule
     * @return array
     */
    private function extractMethod($rule)
    {
        if (!preg_match('/:/', $rule)) {
            return [
                'method' => Str::camel($rule),
                'target' => null,
            ];
        }
        $pos = strpos($rule, ':');
        $target = explode(',', substr($rule, $pos + 1));
        return [
            'method' => Str::camel($rule),
            'target' => (count($target) > 1) ? $target : $target[0],
        ];
    }

    /**
     * Get value from attribute.
     * 
     * @param  string $attributeName
     * @return mixed
     */
    private function getAttributeValue($attributeName)
    {
        $splitAttribute = explode('.', $attributeName);
        if (count($splitAttribute) === 1) {
            return isset(self::$requests[$attributeName]) ? self::$requests[$attributeName] : null;
        }
        $result = self::$requests[array_shift($splitAttribute)];
        foreach ($splitAttribute as $value) {
            if (!isset($result[$value])) {
                $result = null;
                break;
            }
            $result = $result[$value];
        }

        return $result;
    }

    /**
     * Is triggered when validator does not implement rules method.
     * 
     * @throws RuntimeException
     */
    public function rules()
    {
        throw new RuntimeException('Validator does not implement rules method.');
    }

    /**
     * Is triggered when validator does not implement messages method.
     * 
     * @throws RuntimeException
     */
    public function messages()
    {
        if (!empty($this->rules())) {
            throw new RuntimeException('Validator does not implement messages method.');
        }
    }

    /**
     * Return validation error.
     * 
     * @return array
     */
    public function getErrors()
    {
        return self::$errors;
    }

    /**
     * Return validation error.
     * 
     * @param  string $attributeName
     * @return array|string
     */
    public function getError($attributeName)
    {
        if (!isset(self::$errors[$attributeName])) {
            return '';
        }
        if (count(self::$errors[$attributeName]) === 1) {
            return self::$errors[$attributeName][0];
        }
        return self::$errors[$attributeName];
    }

    /**
     * Split rules.
     *
     * @throws InvalidArgumentException if rule is not array or string.
     * @return void
     */
    private function splitRules()
    {
        foreach (self::$rules as $name => &$rules) {
            $type = gettype($rules);
            if ($type != 'string' && $type != 'array') {
                throw new \InvalidArgumentException("Rule for $name needs to be an array or string");
            }
            if ($type !== 'string') {
                continue;
            }
            $rules = explode('|', $rules);
        }
    }

    /**
     * Check if the message for rule is defined, if it is defined, set the error for that rule.
     * 
     * @param  string $attribute
     * @param  string $rule Rule name
     * @throws InvalidArgumentException if the message for rule is not defined.
     * @return void
     */
    protected function setError($attribute, $rule)
    {
        $isNotExistMessage = isset(self::$messages["$attribute.$rule"])
            && !isset(self::$messages["$attribute." . lcfirst($rule)])
            && (lcfirst($rule) != self::BAIL_KEY);

        if ($isNotExistMessage) {
            throw new \InvalidArgumentException("Message for input \"$attribute\" is not defined.");
        }
        $this->setErrorMessage($attribute, self::$messages["$attribute.$rule"] ?: self::$messages["$attribute." . lcfirst($rule)]);
    }

    /**
     * Set error message to attribute.
     * 
     * @param  string $attribute
     * @param  string $message
     * @return void
     */
    protected function setErrorMessage($attribute, $message)
    {
        if ($attribute == self::BAIL_KEY) {
            return;
        }
        if (!isset(self::$errors[$attribute])) {
            self::$errors[$attribute] = array();
        }
        self::$errors[$attribute][] = $message;
    }

    /**
     * Get class instance.
     * 
     * @param  array $arguments if not exist instance, use arguments to create new instance
     * @return static
     */
    public static function getInstance($arguments = [])
    {
        if (!is_array($arguments)) {
            $arguments = $_REQUEST;
        }

        return self::$instance = self::$instance ?: (new static($arguments));
    }

    /**
     * Is triggered when invoking inaccessible methods in an object context.
     */
    public function __call($name, $arguments)
    {
        if ($name == 'validate') {
            return $this->execValidate();
        } elseif ($name == 'errors') {
            return $this->getErrors();
        }
    }
}
