Please clone the repository.

Install the Composer packages:

```bash
composer install
```

We also need to run npm:

```bash
npm install
```

To run the code, simply run Composer run dev. This will run the frontend and backend code.

The site can be accessed from `http://127.0.0.1:8000/`

To test the code:
```bash
./vendor/bin/phpunit tests/Unit/StampDutyCalculatorTest.php --no-coverage
```

Example runs:

- Property value: £300,000.00 → Stamp Duty: £5,000.00
- Property value: £250,000.00 → Stamp Duty: £2,500.00

First-time buyer:
- Property value: £250,000.00 → Stamp Duty: £0

Additional Property
- Property value: £500,000.00 → Stamp Duty: £40,00.00
- Property value: £250,000.00 → Stamp Duty: £15,00.00