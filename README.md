# Mobile Money SMS Parser

Parse mobile money SMS notifications to extract transaction data. Supports Orange Money and Moov Money (West Africa / FCFA zone).

## Installation

```bash
composer require kulturman/mobile-money-sms-parser
```

If the package is not on Packagist, add the repository to your `composer.json`:

```json
"repositories": [
    {"type": "vcs", "url": "https://github.com/kulturman/mobile-money-sms-parser"}
]
```

## Usage

### With the Factory (recommended)

```php
use Kulturman\MobileMoneyParser\SmsParserFactory;

$factory = new SmsParserFactory();

// Get the right parser based on sender ID
$parser = $factory->getParser('OrangeMoney');

$result = $parser->parse('Vous avez recu un paiement de 5 000 FCFA du numero 22670123456. Ref: INV-2025-001. Trans ID: CI241227.1234.A00001');

$result->amount;        // 5000
$result->reference;     // "INV-2025-001"
$result->transactionId; // "CI241227.1234.A00001"
$result->senderPhone;   // "22670123456"
```

### Direct parser usage

```php
use Kulturman\MobileMoneyParser\Parsers\OrangeMoneyParser;
use Kulturman\MobileMoneyParser\Parsers\MoovMoneyParser;

$parser = new OrangeMoneyParser();
$result = $parser->parse($smsText);

$parser = new MoovMoneyParser();
$result = $parser->parse($smsText);
```

### Check provider support

```php
$factory = new SmsParserFactory();

$factory->supportsProvider('OrangeMoney'); // true
$factory->supportsProvider('MoovMoney');   // true
$factory->supportsProvider('Wave');        // false
```

### Error handling

```php
use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;

try {
    $result = $parser->parse($smsText);
} catch (SmsParsingException $e) {
    // Amount or reference could not be extracted
}
```

### Convert to array

```php
$result = $parser->parse($smsText);

$result->toArray();
// [
//     'amount' => 5000,
//     'reference' => 'INV-2025-001',
//     'transaction_id' => 'CI241227.1234.A00001',
//     'sender_phone' => '22670123456',
// ]
```

## Supported SMS formats

### Orange Money

- Amount: `5 000 FCFA`, `5,000 FCFA`, `5000 FCFA`
- Reference: `Ref: XXX`, `Reference: XXX`, `Motif: XXX`
- Transaction ID: `Trans ID: CI241227.1234.A00001`, `ID: TXN123`
- Phone: `du numero 22670123456`, `de 22670123456`

### Moov Money

- Amount: `Montant:5 000,00FCFA`, `Montant:100,00FCFA`
- Reference: `Ref: XXX`, `Reference: XXX`, `Motif: XXX`
- Transaction ID: `TID: MP230709.0057.C00022`, `Trans ID: XXX`
- Phone: `Numero:22672606628`, `de 22660123456`

## Adding a new provider

1. Create a parser class implementing `SmsParserInterface`
2. Add the provider to the `MobileMoneyProvider` enum
3. Register it in `SmsParserFactory`

```php
use Kulturman\MobileMoneyParser\Contracts\SmsParserInterface;
use Kulturman\MobileMoneyParser\DTOs\ParsedSmsDTO;

class WaveParser implements SmsParserInterface
{
    public function parse(string $smsText): ParsedSmsDTO
    {
        // Your parsing logic
    }
}
```

## Requirements

- PHP 8.2+

## Testing

```bash
composer test
```

## License

MIT
