<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Pix = 'pix';
    case Cash = 'dinheiro';
    case CreditCard = 'cartao_credito';
    case DebitCard = 'cartao_debito';

    public function label(): string
    {
        return match ($this) {
            self::Pix => 'Pix',
            self::Cash => 'Dinheiro',
            self::CreditCard => 'Cartão de Crédito',
            self::DebitCard => 'Cartão de Débito',
        };
    }
}
