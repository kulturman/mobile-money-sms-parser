<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\Parsers;

use Kulturman\MobileMoneyParser\Contracts\SmsParserInterface;
use Kulturman\MobileMoneyParser\DTOs\ParsedSmsDTO;
use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;

class MoovMoneyParser implements SmsParserInterface
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
        // Format Moov: Montant:100,00FCFA ou Montant:1 000,00FCFA
        if (preg_match('/Montant:\s*([\d\s]+)(?:,\d{2})?\s*FCFA/i', $smsText, $matches)) {
            return (int) preg_replace('/\s/', '', $matches[1]);
        }

        // Format alternatif: recu 5000 FCFA ou 5 000 FCFA
        if (preg_match('/(\d[\d\s]*)\s*FCFA/i', $smsText, $matches)) {
            return (int) preg_replace('/\s/', '', $matches[1]);
        }

        throw new SmsParsingException('Unable to extract amount from SMS');
    }

    private function extractReference(string $smsText): string
    {
        // Support pour "Ref:", "Reference:" et "Motif:" (format local BF)
        if (preg_match('/(?:Ref|Reference|Motif)\s*:\s*([A-Za-z0-9\-_]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        throw new SmsParsingException('Unable to extract reference from SMS');
    }

    private function extractTransactionId(string $smsText): ?string
    {
        // Format Moov: TID: MP230709.0057.C00022
        if (preg_match('/TID:\s*([A-Z0-9\.]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        // Format alternatif: ID: MVTXN123 ou Trans ID: XXX
        if (preg_match('/(?:ID|Trans ID|Transaction)\s*:\s*([A-Za-z0-9]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractSenderPhone(string $smsText): ?string
    {
        // Format Moov: Numero:22672606628
        if (preg_match('/Numero:\s*(\d+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        // Format alternatif: de 22660123456
        if (preg_match('/(?:de|depuis)\s+(\d{11,14})/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
