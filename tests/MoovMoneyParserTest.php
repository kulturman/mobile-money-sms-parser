<?php

declare(strict_types=1);

use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;
use Kulturman\MobileMoneyParser\Parsers\MoovMoneyParser;

beforeEach(function () {
    $this->parser = new MoovMoneyParser;
});

it('parses a complete Moov Money SMS', function () {
    $sms = 'Vous avez recu un paiement. Montant:5 000,00FCFA. Numero:22672606628. Ref: SCH-2025-001. TID: MP230709.0057.C00022';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(5000)
        ->and($result->reference)->toBe('SCH-2025-001')
        ->and($result->transactionId)->toBe('MP230709.0057.C00022')
        ->and($result->senderPhone)->toBe('22672606628');
});

it('parses Moov format with Montant prefix', function () {
    $sms = 'Paiement recu. Montant:100,00FCFA. Ref: TEST-001';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(100)
        ->and($result->reference)->toBe('TEST-001');
});

it('parses large amounts with spaces', function () {
    $sms = 'Paiement recu. Montant:1 500 000,00FCFA. Motif: TERRAIN-LOT42';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(1500000)
        ->and($result->reference)->toBe('TERRAIN-LOT42');
});

it('falls back to alternative amount format', function () {
    $sms = 'Vous avez recu 25 000 FCFA. Ref: PAY-200';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(25000);
});

it('returns null for optional fields when not present', function () {
    $sms = 'Montant:5000,00FCFA. Ref: TEST-002';

    $result = $this->parser->parse($sms);

    expect($result->transactionId)->toBeNull()
        ->and($result->senderPhone)->toBeNull();
});

it('throws exception when amount is missing', function () {
    $sms = 'Vous avez recu un paiement. Ref: TEST-001';

    $this->parser->parse($sms);
})->throws(SmsParsingException::class, 'Unable to extract amount from SMS');

it('returns null when reference is missing', function () {
    $sms = 'Montant:5000,00FCFA';

    $result = $this->parser->parse($sms);

    expect($result->reference)->toBeNull();
});

it('parses Moov Money SMS with decimal comma and accented Numéro', function () {
    $sms = "Vous avez reçu 10 100,00 FCFA de K C THEOPHILE GNOUMOU. \nNuméro: 22672569829\nDate: 29/01/2026 16:09:54\nTID: DAT362O1CX\nSolde: 17 669,00 FCFA";

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(10100)
        ->and($result->senderPhone)->toBe('22672569829')
        ->and($result->transactionId)->toBe('DAT362O1CX');
});

it('converts parsed result to array', function () {
    $sms = 'Montant:75 000,00FCFA. Numero:22660111222. Ref: LOYER-MAR25. TID: MP250301.1234.D00001';

    $result = $this->parser->parse($sms);

    expect($result->toArray())->toBe([
        'amount' => 75000,
        'reference' => 'LOYER-MAR25',
        'transaction_id' => 'MP250301.1234.D00001',
        'sender_phone' => '22660111222',
    ]);
});
