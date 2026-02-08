<?php

declare(strict_types=1);

use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;
use Kulturman\MobileMoneyParser\Parsers\OrangeMoneyParser;

beforeEach(function () {
    $this->parser = new OrangeMoneyParser;
});

it('parses a complete Orange Money SMS', function () {
    $sms = 'Vous avez recu un paiement de 5 000 FCFA du numero 22670123456. Ref: SCH-2025-001. Trans ID: CI241227.1234.A00001';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(5000)
        ->and($result->reference)->toBe('SCH-2025-001')
        ->and($result->transactionId)->toBe('CI241227.1234.A00001')
        ->and($result->senderPhone)->toBe('22670123456');
});

it('parses amount with comma separator', function () {
    $sms = 'Paiement de 15,000 FCFA recu. Ref: PAY-100';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(15000)
        ->and($result->reference)->toBe('PAY-100');
});

it('parses amount with space separator', function () {
    $sms = 'Paiement de 150 000 FCFA recu. Motif: INSCRIPTION-2025';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(150000)
        ->and($result->reference)->toBe('INSCRIPTION-2025');
});

it('returns null for optional fields when not present', function () {
    $sms = 'Paiement de 5000 FCFA recu. Ref: TEST-001';

    $result = $this->parser->parse($sms);

    expect($result->transactionId)->toBeNull()
        ->and($result->senderPhone)->toBeNull();
});

it('throws exception when amount is missing', function () {
    $sms = 'Vous avez recu un paiement. Ref: TEST-001';

    $this->parser->parse($sms);
})->throws(SmsParsingException::class, 'Unable to extract amount from SMS');

it('throws exception when reference is missing', function () {
    $sms = 'Paiement de 5000 FCFA recu.';

    $this->parser->parse($sms);
})->throws(SmsParsingException::class, 'Unable to extract reference from SMS');

it('converts parsed result to array', function () {
    $sms = 'Paiement de 10 000 FCFA du numero 22670999888. Ref: INV-50. Trans ID: CI250101.5678.B00002';

    $result = $this->parser->parse($sms);

    expect($result->toArray())->toBe([
        'amount' => 10000,
        'reference' => 'INV-50',
        'transaction_id' => 'CI250101.5678.B00002',
        'sender_phone' => '22670999888',
    ]);
});
