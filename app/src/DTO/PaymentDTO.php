<?php

namespace App\DTO;

class PaymentDTO extends DTO
{
    public string $paymentDate;
    public string $firstName;
    public string $lastName;
    public float $amount;
    public ?string $nationalSecurityNumber;
    public string $description;
    public string $refId;
    public string $loanNumber;
}
