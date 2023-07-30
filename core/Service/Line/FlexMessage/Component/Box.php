<?php

namespace Core\Service\Line\FlexMessage\Component;

use TypeError;
use UnexpectedValueException;
use Core\Service\Line\FlexMessage\Enum\LayoutType;
use Core\Service\Line\FlexMessage\Component\MarginTrait;
use Core\Service\Line\FlexMessage\Component\BoxContentInterface;

class Box implements BoxContentInterface
{
    use MarginTrait;
    private const TYPE = 'box';

    protected array $contents;

    protected LayoutType|string $layout = LayoutType::Vertical;

    public function __construct(array $contents = [])
    {
        $this->contents = $contents;
    }

    /**
     * Add content
     *
     * @param  BoxContentInterface|array  $content
     * @return array
     */
    public function addContent(BoxContentInterface|array $content)
    {
        if (is_array($content)) {
            if (empty($content)) {
                throw new UnexpectedValueException(sprintf('%s: Argument #1 ($content) can not be empty', __METHOD__));
            }
            foreach ($content as $key => $value) {
                if (!$value instanceof BoxContentInterface) {
                    throw new TypeError(sprintf('%s: Argument #1 ($content[%d]) must be of type %s, %s given', __METHOD__, $key, BoxContentInterface::class, gettype($value)));
                }
                $this->contents[] = $value;
            }
        } else {
            $this->contents[] = $content;
        }

        return $this;
    }

    /**
     * Set layout
     *
     * @param  \Core\Service\Line\FlexMessage\Enum\LayoutType|string  $action
     * @return $this
     */
    public function layout(LayoutType|string $layout)
    {
        $layouts = array_column(LayoutType::cases(), 'value');

        if (is_string($layout) && !in_array($layout, $layouts)) {
            throw new UnexpectedValueException(sprintf('Argument #1 ($layouts) must be one of the following values: %s', implode(", ", $layouts)));
        }

        $this->layout = $layout;

        return $this;
    }

    public function toJson()
    {

    }

    public function toArray(): array
    {
        $value =  [
            "type" => self::TYPE,
            "layout" => $this->layout,
            "contents" => array_map(function($content) {
                return $content->toArray();
            }, $this->contents)
        ];
        if (isset($this->margin)) {
            $value['margin'] = $this->margin;
        }

        return $value;
    }
}
