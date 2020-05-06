<?php

namespace Rubix\ML\Tests\Transformers;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Transformers\Transformer;
use Rubix\ML\Transformers\ExtraWordsFilter;
use PHPUnit\Framework\TestCase;

/**
 * @group Transformers
 * @covers \Rubix\ML\Transformers\StopWordFilter
 */
class ExtraWordsFilterTest extends TestCase
{
    /**
     * @var \Rubix\ML\Datasets\Unlabeled
     */
    protected $dataset;

    /**
     * @var \Rubix\ML\Transformers\StopWordFilter
     */
    protected $transformer;

    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->dataset = Unlabeled::quick([
            ['The the quick quick brown fox jumped over the lazy man sitting at a bus stop'],
        ]);

        $this->transformer = new ExtraWordsFilter();
    }

    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(ExtraWordsFilter::class, $this->transformer);
        $this->assertInstanceOf(Transformer::class, $this->transformer);
    }

    /**
     * @test
     */
    public function transform() : void
    {
        $this->dataset->apply($this->transformer);

        $expected = [
            ['The quick brown fox jumped over the lazy man sitting at a bus stop'],
        ];

        $this->assertEquals($expected, $this->dataset->samples());
    }
}
