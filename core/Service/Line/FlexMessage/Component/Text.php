<?php

namespace Core\Service\Line\FlexMessage\Component;

use Core\Support\Helper\Str;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Enum\FontSize;
use Core\Service\Line\FlexMessage\Enum\FontStyle;
use Core\Service\Line\FlexMessage\Enum\FontWeight;
use Core\Service\Line\FlexMessage\Enum\Decoration;
use Core\Service\Line\FlexMessage\Component\FlexTrait;
use Core\Service\Line\FlexMessage\Component\ColorTrait;
use Core\Service\Line\FlexMessage\Component\Action\ActionInterface;

class Text implements BoxContentInterface
{
    use FlexTrait, ColorTrait;

    private const TYPE = 'text';

    /**
     * Text
     *
     * @var string
     */
    protected string $text;

    /**
     * Wrap text.
     *
     * @var bool
     */
    protected bool $wrap = false;

    /**
     * Font size
     *
     * @var \Core\Service\Line\FlexMessage\Enum\FontSize|string
     */
    protected FontSize|string $size;

    /**
     * Style of the text.
     *
     * @var \Core\Service\Line\FlexMessage\Enum\Decoration|string
     */
    protected Decoration|string $decoration = Decoration::None;

    /**
     * Style of the text.
     *
     * @var \Core\Service\Line\FlexMessage\Enum\FontStyle|string
     */
    protected FontStyle|string $style = FontStyle::Normal;

    /**
     * Font weight
     *
     * @var \Core\Service\Line\FlexMessage\Enum\FontWeight|string
     */
    protected FontWeight|string $weight = FontWeight::Regular;

    /**
     * Action
     *
     * @var \Core\Service\Line\FlexMessage\Component\Action\ActionInterface
     */
    protected ActionInterface $action;

    /**
     * Set text. Be sure to set either one of the text property or contents
     * property. If you set the contents property, text is ignored.
     *
     * @param  string  $text
     * @return $this
     */
    public function text(string $text)
    {
        if (Str::isEmpty($text)) {
            throw new UnexpectedValueException('Argument #1 ($text) can not be empty');
        }

        $this->text = $text;

        return $this;
    }

    /**
     * Set font size
     *
     * @param  \Core\Service\Line\FlexMessage\Enum\FontSize|string  $text
     * @return $this
     */
    public function size(FontSize|string $size)
    {
        if (is_string($size)) {
            if (
                !in_array($size, array_column(FontSize::cases(), 'value'))
                && (!Str::endsWith($size, 'px')
                    || !is_numeric(Str::before($size, 'px'))
                    || Str::before($size, 'px') < "0")
            ) {
                throw new UnexpectedValueException('Argument #1 ($size) must be a positive integer or decimal number that ends in px. Examples include 50px and 23.5px');
            }
        }

        $this->size = $size;

        return $this;
    }

    /**
     * Set font weight
     *
     * @param  \Core\Service\Line\FlexMessage\Enum\FontWeight|string  $weight
     * @return $this
     */
    public function weight(FontWeight|string $weight)
    {
        $fontWeights = array_column(FontWeight::cases(), 'value');

        if (is_string($weight) && !in_array($weight, $fontWeights)) {
            throw new UnexpectedValueException(sprintf('Argument #1 ($weight) must be one of the following values: %s', implode(", ", $fontWeights)));
        }

        $this->weight = $weight;

        return $this;
    }

    /**
     * Set style of the text.
     *
     * @param  \Core\Service\Line\FlexMessage\Enum\FontWeight|string  $style
     * @return $this
     */
    public function style(FontStyle|string $style)
    {
        $fontStyle = array_column(FontStyle::cases(), 'value');

        if (is_string($style) && !in_array($style, $fontStyle)) {
            throw new UnexpectedValueException(sprintf('Argument #1 ($style) must be one of the following values: %s', implode(", ", $fontStyle)));
        }

        $this->style = $style;

        return $this;
    }

    /**
     * Set decoration of the text.
     *
     * @param  \Core\Service\Line\FlexMessage\Enum\Decoration|string  $decoration
     * @return $this
     */
    public function decoration(Decoration|string $decoration)
    {
        $decorations = array_column(Decoration::cases(), 'value');

        if (is_string($decoration) && !in_array($decoration, $decorations)) {
            throw new UnexpectedValueException(sprintf('Argument #1 ($decoration) must be one of the following values: %s', implode(", ", $decorations)));
        }

        $this->decoration = $decoration;

        return $this;
    }

    /**
     * Set action
     *
     * @param  \Core\Service\Line\FlexMessage\Component\Action\ActionInterface  $action
     * @return $this
     */
    public function action(ActionInterface $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Wrap text. If set to true, you can use a new line character (\n) to begin on a new line.
     *
     * @param  bool  $wrap
     * @return $this
     */
    public function wrap(bool $wrap)
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function toArray(): array
    {
        $values = [
            "wrap"  => $this->wrap
        ];
        if (isset($this->flex)) {
            $values['flex'] = $this->flex;
        }
        if (!empty($this->action)) {
            $values['action'] = $this->action->toArray();
        }

        return array_merge(["type" => self::TYPE], get_object_vars($this), $values);
    }
}
