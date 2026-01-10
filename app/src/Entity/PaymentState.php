<?php

namespace App\Entity;

enum PaymentState: string
{
    case ASSIGNED = 'ASSIGNED';
    case PARTIALLY_ASSIGNED = 'PARTIALLY_ASSIGNED';
    case REFUND = 'REFUND';
}
