<?php

namespace Rubix\ML\Transformers;

use Rubix\ML\DataType;
use Stringable;

/**
 * ExtraWordsFilter
 *
 * Filters extra (repeating) words in the dataset.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class ExtraWordsFilter implements Transformer, Stringable
{
    /**
     * A pattern to match 'extra words'.
     *
     * @var string
     */
    protected const EXTRA_WORDS_REGEX = '/\b(\w+)\s+\\1+\b/i';

    /**
     * A whitespace character.
     *
     * @var string
     */
    protected const REPLACEMENT = '$1';

    /**
     * Return the data types that this transformer is compatible with.
     *
     * @return \Rubix\ML\DataType[]
     */
    public function compatibility() : array
    {
        return DataType::all();
    }

    /**
     * Transform the dataset in place.
     *
     * @param array[] $samples
     */
    public function transform(array &$samples) : void
    {
        foreach ($samples as &$sample) {
            foreach ($sample as &$value) {
                if (is_string($value)) {
                    $value = preg_replace(self::EXTRA_WORDS_REGEX, self::REPLACEMENT, $value);
                }
            }
        }
    }

    /**
     * Return the string representation of the object.
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Extra Words Filter';
    }
}
