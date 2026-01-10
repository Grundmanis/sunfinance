<?php

namespace App\Validation;

enum ValidationErrorType: string
{
    case VALIDATION = 'validation';
    case NOT_FOUND = 'notFound';
    case DUPLICATE = 'duplicate';
}
