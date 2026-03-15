<?php

declare(strict_types=1);

use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;
use Kulturman\MobileMoneyParser\Parsers\TelecelMoneyParser;

beforeEach(function () {
    $this->parser = new TelecelMoneyParser;
});

it('parses a complete Telecel Money SMS with sender name', function () {
    $sms = 'Vous avez recu 5000.00 FCFA du 22678049078 - GUIETAWINDE JUNIOR ROUAMBA le 02/03/2026 22:28:22. Trans ID : DC293XMGC3. Votre solde est de 62325.00 FCFA';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(5000)
        ->and($result->transactionId)->toBe('DC293XMGC3')
        ->and($result->senderPhone)->toBe('22678049078')
        ->and($result->reference)->toBeNull();
});

it('parses a Telecel Money payment SMS with Montant/Frais format', function () {
    $sms = "Vous avez recu un paiement du 79641224 ce 15/03/2026 08:21:04.\nMontant: 1000.00 FCFA \nFrais: 0.00 FCFA.\n\nNouveau solde: 1000.00 FCFA. ID de transaction est DCF2413W02.";

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(1000)
        ->and($result->transactionId)->toBe('DCF2413W02')
        ->and($result->senderPhone)->toBe('79641224')
        ->and($result->reference)->toBeNull();
});

it('parses large amounts', function () {
    $sms = 'Vous avez recu 150000.00 FCFA du 22670001122 - NOM PRENOM le 01/01/2026 10:00:00. Trans ID : AB123XYZ99. Votre solde est de 200000.00 FCFA';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(150000);
});

it('returns null for optional fields when not present', function () {
    $sms = 'Vous avez recu 5000.00 FCFA quelque part';

    $result = $this->parser->parse($sms);

    expect($result->amount)->toBe(5000)
        ->and($result->transactionId)->toBeNull()
        ->and($result->senderPhone)->toBeNull()
        ->and($result->reference)->toBeNull();
});

it('throws exception when amount is missing', function () {
    $sms = 'Vous avez recu un paiement du 79641224 ce 15/03/2026.';

    $this->parser->parse($sms);
})->throws(SmsParsingException::class, 'Unable to extract amount from SMS');

it('extracts reference when present', function () {
    $sms = 'Vous avez recu 5000.00 FCFA du 22678049078 - NOM le 02/03/2026 22:28:22. Trans ID : DC293XMGC3. Ref: PAY-2026-001';

    $result = $this->parser->parse($sms);

    expect($result->reference)->toBe('PAY-2026-001');
});

it('converts parsed result to array', function () {
    $sms = 'Vous avez recu 5000.00 FCFA du 22678049078 - GUIETAWINDE JUNIOR ROUAMBA le 02/03/2026 22:28:22. Trans ID : DC293XMGC3. Votre solde est de 62325.00 FCFA';

    $result = $this->parser->parse($sms);

    expect($result->toArray())->toBe([
        'amount' => 5000,
        'reference' => null,
        'transaction_id' => 'DC293XMGC3',
        'sender_phone' => '22678049078',
    ]);
});
