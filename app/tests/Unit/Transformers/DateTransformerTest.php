<?php

namespace Tests\Transformers;

use App\Transformers\DateTransformer;
use PHPUnit\Framework\TestCase;

class DateTransformerTest extends TestCase
{
    private DateTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new DateTransformer();
    }

    public function testTransformValidDate(): void
    {
        $this->assertEquals('2022-12-12 07:16:29', $this->transformer->transform('20221212071629'));
        $this->assertEquals('2022-12-14 11:20:45', $this->transformer->transform('Wed, 14 Dec 2022 11:20:45 +0000'));
    }

    public function testTransformInvalidDate(): void
    {
        $this->assertNull($this->transformer->transform('invalid-date'));
        $this->assertNull($this->transformer->transform('12345'));
    }
}
