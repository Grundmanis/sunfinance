<?php

namespace App\Entity;

enum LoanState: string
{
    case PENDING = 'PENDING';
    case ACTIVE = 'ACTIVE';
    case PAID = 'PAID';
    case DEFAULTED = 'DEFAULTED';
}
