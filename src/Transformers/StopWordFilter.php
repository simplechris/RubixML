<?php

namespace Rubix\ML\Transformers;

use InvalidArgumentException;

use function gettype;

/**
 * Stop Word Filter
 *
 * Removes user-specified words from any categorical feature columns including blobs
 * of text.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class StopWordFilter extends RegexFilter
{
    public const DEFAULT_EN = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'if', 'in', 'into', 'is', 'it',
        'no', 'not', 'of', 'on', 'or', 'such', 'that', 'the', 'their', 'then', 'there', 'these', 'they',
        'this', 'to', 'was', 'will', 'with',
    ];

    /**
     * @param string[] $stopWords
     * @throws \InvalidArgumentException
     */
    public function __construct(array $stopWords = [])
    {
        $patterns = [];

        foreach ($stopWords as $word) {
            if (!is_string($word)) {
                throw new InvalidArgumentException('Stop word must be a'
                    . ' string, ' . gettype($word) . ' found.');
            }

            $patterns[] = '/\b' . preg_quote($word, '/') . '\b/u';
        }

        parent::__construct($patterns);
    }

    /**
     * Return the string representation of the object.
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Stop Word Filter';
    }
}
