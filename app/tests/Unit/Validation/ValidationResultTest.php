<?php

namespace Tests\Unit\Validation;

use App\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

class ValidationResultTest extends TestCase
{
    public function testValidationResultIsValid(): void
    {
        $valid = true;
        $errors = [];

        $validationResult = new ValidationResult($valid, $errors);

        $this->assertTrue($validationResult->isValid());
        $this->assertEmpty($validationResult->getErrors());
    }

    public function testValidationResultIsInvalid(): void
    {
        $valid = false;
        $errors = ['Error 1', 'Error 2'];

        $validationResult = new ValidationResult($valid, $errors);

        $this->assertFalse($validationResult->isValid());
        $this->assertEquals($errors, $validationResult->getErrors());
    }
}
