<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\Parsers;

use Kulturman\MobileMoneyParser\Contracts\SmsParserInterface;
use Kulturman\MobileMoneyParser\DTOs\ParsedSmsDTO;
use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;

class TelecelMoneyParser implements SmsParserInterface
{
    public function parse(string $smsText): ParsedSmsDTO
    {
        $amount = $this->extractAmount($smsText);
        $reference = $this->extractReference($smsText);
        $transactionId = $this->extractTransactionId($smsText);
        $senderPhone = $this->extractSenderPhone($smsText);

        return new ParsedSmsDTO(
            amount: $amount,
            reference: $reference,
            transactionId: $transactionId,
            senderPhone: $senderPhone,
        );
    }

    private function extractAmount(string $smsText): int
    {
        // Format 1: "recu 5000.00 FCFA"
        if (preg_match('/recu\s+(\d[\d\s]*(?:\.\d{2})?)\s*FCFA/i', $smsText, $matches)) {
            return (int) preg_replace('/[\s.].*$|[\s]/', '', $matches[1]);
        }

        // Format 2: "Montant: 1000.00 FCFA"
        if (preg_match('/Montant:\s*(\d[\d\s]*(?:\.\d{2})?)\s*FCFA/i', $smsText, $matches)) {
            return (int) preg_replace('/[\s.].*$|[\s]/', '', $matches[1]);
        }

        throw new SmsParsingException('Unable to extract amount from SMS');
    }

    private function extractReference(string $smsText): ?string
    {
        if (preg_match('/(?:Ref|Reference|Motif)\s*:\s*([A-Za-z0-9\-_]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractTransactionId(string $smsText): ?string
    {
        // Format: "Trans ID : DC293XMGC3" ou "ID de transaction est DCF2413W02"
        if (preg_match('/Trans\s*ID\s*:\s*([A-Za-z0-9]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        if (preg_match('/ID\s+de\s+transaction\s+est\s+([A-Za-z0-9]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractSenderPhone(string $smsText): ?string
    {
        // Format 1: "du 22678049078 -" (avec indicatif)
        if (preg_match('/du\s+(\d{11,14})\s*-/i', $smsText, $matches)) {
            return $matches[1];
        }

        // Format 2: "du 79641224 ce" (sans indicatif)
        if (preg_match('/du\s+(\d{8,14})(?:\s+ce|\s*-)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
