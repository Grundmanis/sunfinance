<?php

namespace Tests\Unit\Transformers;

use App\Utils\DateUtil;
use PHPUnit\Framework\TestCase;

class DateUtilTest extends TestCase
{

    public function testTransformValidDate(): void
    {
        $this->assertEquals('2022-12-12 07:16:29', DateUtil::transform('20221212071629'));
        $this->assertEquals('2022-12-14 11:20:45', DateUtil::transform('Wed, 14 Dec 2022 11:20:45 +0000'));
    }

    public function testTransformInvalidDate(): void
    {
        $this->assertNull(DateUtil::transform('invalid-date'));
        $this->assertNull(DateUtil::transform('12345'));
    }
}
