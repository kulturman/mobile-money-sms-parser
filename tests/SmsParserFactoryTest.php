<?php

declare(strict_types=1);

use Kulturman\MobileMoneyParser\Enums\MobileMoneyProvider;
use Kulturman\MobileMoneyParser\Parsers\MoovMoneyParser;
use Kulturman\MobileMoneyParser\Parsers\OrangeMoneyParser;
use Kulturman\MobileMoneyParser\SmsParserFactory;

beforeEach(function () {
    $this->factory = new SmsParserFactory;
});

it('returns OrangeMoneyParser for Orange Money sender', function () {
    $parser = $this->factory->getParser('OrangeMoney');

    expect($parser)->toBeInstanceOf(OrangeMoneyParser::class);
});

it('returns MoovMoneyParser for Moov Money sender', function () {
    $parser = $this->factory->getParser('MoovMoney');

    expect($parser)->toBeInstanceOf(MoovMoneyParser::class);
});

it('returns null for unknown sender', function () {
    $parser = $this->factory->getParser('UnknownProvider');

    expect($parser)->toBeNull();
});

it('identifies provider from sender id', function () {
    expect($this->factory->getProvider('orangemoney'))->toBe(MobileMoneyProvider::ORANGE_MONEY)
        ->and($this->factory->getProvider('moovmoney'))->toBe(MobileMoneyProvider::MOOV_MONEY)
        ->and($this->factory->getProvider('unknown'))->toBeNull();
});

it('checks if provider is supported', function () {
    expect($this->factory->supportsProvider('OrangeMoney'))->toBeTrue()
        ->and($this->factory->supportsProvider('MoovMoney'))->toBeTrue()
        ->and($this->factory->supportsProvider('WaveMoney'))->toBeFalse();
});

it('handles sender id with spaces', function () {
    $parser = $this->factory->getParser('Orange Money');

    expect($parser)->toBeInstanceOf(OrangeMoneyParser::class);
});

it('handles sender id case insensitivity', function () {
    $parser = $this->factory->getParser('ORANGEMONEY');

    expect($parser)->toBeInstanceOf(OrangeMoneyParser::class);
});
