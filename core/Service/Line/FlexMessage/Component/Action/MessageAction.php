<?php

namespace Core\Service\Line\FlexMessage\Component\Action;

use LogicException;
use Core\Support\Helper\Str;
use UnexpectedValueException;

class MessageAction implements ActionInterface
{
    private const TYPE = 'message';
    public const TEXT_MAX_LENGTH = 300;
    public const LABEL_MAX_LENGTH = 40;

    /**
     * Text sent when the action is performed. Max character limit: 300
     *
     * @var string
     */
    protected string $text;

    /**
     * Label for the action.
     *
     * @var string
     */
    protected ?string $label = null;

    /**
     * Set text.
     *
     * @param  string  $text  Max character limit: 40
     * @return $this
     */
    public function text(string $text)
    {
        if (Str::isEmpty($text)) {
            throw new UnexpectedValueException('Argument #1 ($text) can not be empty');
        }

        if (Str::length($text) > self::TEXT_MAX_LENGTH) {
            throw new UnexpectedValueException(sprintf('Length of argument #1 ($text) cannot exceed %d', self::TEXT_MAX_LENGTH));
        }

        $this->text = $text;

        return $this;
    }

    /**
     * Set label.
     *
     * @param  string  $label  Max character limit
     * @return $this
     */
    public function label(string $label)
    {
        if (Str::isEmpty($label)) {
            throw new UnexpectedValueException('Argument #1 ($label) can not be empty');
        }

        if (Str::length($label) > self::LABEL_MAX_LENGTH) {
            throw new UnexpectedValueException(sprintf('Length of argument #1 ($label) cannot exceed %d', self::LABEL_MAX_LENGTH));
        }

        $this->label = $label;

        return $this;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toArray(): array
    {
        if (Str::isEmpty($this->text)) {
            throw new LogicException('Please set action text first.');
        }

        return array_merge(["type" => self::TYPE], array_filter(get_object_vars($this)));
    }
}
