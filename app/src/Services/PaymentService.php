<?php

namespace App\Services;


// 3. Save to store with following logic:
//    When payment amount equals to matched loan amount to pay

//    - Mark loan as paid
//    - Mark payment as assigned
//      When payment amount is greater than matched loan amount to pay
//    - Mark loan as paid
//    - Mark payment as partially assigned
//    - Create refund payment as separate entity called "Payment Order" with all necessary information
//      When payment amount is less than matched load amount to pay
//    - Mark payment as assigned



class PaymentService {}
