<?php

namespace Tests\Unit\Transformers;

use App\Utils\DateTransformer;
use PHPUnit\Framework\TestCase;

class DateTransformerTest extends TestCase
{

    public function testTransformValidDate(): void
    {
        $this->assertEquals('2022-12-12 07:16:29', DateTransformer::transform('20221212071629'));
        $this->assertEquals('2022-12-14 11:20:45', DateTransformer::transform('Wed, 14 Dec 2022 11:20:45 +0000'));
    }

    public function testTransformInvalidDate(): void
    {
        $this->assertNull(DateTransformer::transform('invalid-date'));
        $this->assertNull(DateTransformer::transform('12345'));
    }
}
