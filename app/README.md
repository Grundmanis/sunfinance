# Homework task description

Implement small application which will be able to handle payment import CSV files (batches) and incoming API calls (single payment).

Data to work with can be found in `exampleData/` folder.

Payment import and API request has loan number in description. Consists of 2 letters and 8 numbers, starts with LN.

### Business requirements

#### Payment processing

Validation before creation
   - duplicate entry (paymentReference or refId)
   - negative amount
   - invalid date

Save to the storage

#### Payment assignment

When payment amount equals to matched loan amount to pay
   - Mark loan as paid
   - Mark payment as assigned

When payment amount is greater than matched loan amount to pay
   - Mark loan as paid
   - Mark payment as partially assigned
   - Create refund payment as separate entity called "Payment Order" with all necessary information

When payment amount is less than matched load amount to pay
   - Mark payment as assigned

Track paid amounts on loan

#### Console interface
- Show payments by date `report --date=YYYY-MM-DD`

#### Communication functionality implementation (abstraction layer is enough)
- Payment received: communication sent to email and|or phone if defined
- Loan fully paid: communication sent to email and|or phone if defined
- Failed payments report: support@example.com


_If rest of the task takes too much time, skip this requirement._


### Technical requirements

1. PHP;
2. Data storage could be anything: files, SQLite, PostgreSQL, etc.
3. Use only libraries and components that are necessary. No need to use full framework.
4. Two entry points to import payment(-s)
    1. API (single payment) - **{app_url}/api/payment**
    2. Console (CSV payment batch) - **import --file=<FILE_PATH>**
5. Different error handling
   - API: Duplicate entry - 409, rest of errors - 400, All fine - 2XX depending on implementation
   - CONSOLE: Duplicate entry - 1, Negative amount - 2, Invalid date - 3, All fine - 0
6. Add logging

### Extra points

1. Containerability
2. Scalability
3. Testing
4. Documentation
