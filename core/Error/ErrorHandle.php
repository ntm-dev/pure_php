<?php

namespace Core\Error;

class ErrorHandle extends \Error
{
    private array $error;

    /**
     * {@inheritdoc}
     *
     * @param array $error An array as returned by error_get_last()
     */
    public function __construct(string $message, int $code, array $error, int $traceOffset = null, bool $traceArgs = true, array $trace = null)
    {
        parent::__construct($message, $code);

        $this->error = $error;

        if (null !== $trace) {
            if (!$traceArgs) {
                foreach ($trace as &$frame) {
                    unset($frame['args'], $frame['this'], $frame);
                }
            }
        } elseif (null !== $traceOffset) {
            $trace = [];
        }

        foreach ([
            'file' => $error['file'],
            'line' => $error['line'],
            'trace' => $trace,
        ] as $property => $value) {
            if (null !== $value) {
                $refl = new \ReflectionProperty(\Error::class, $property);
                $refl->setAccessible(true);
                $refl->setValue($this, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): array
    {
        return $this->error;
    }
}
