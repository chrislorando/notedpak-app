<?php

namespace App\Enums;

enum Category: string
{
    case Red = 'red';
    case Blue = 'blue';
    case Green = 'green';
    case Orange = 'orange';
    case Yellow = 'yellow';

    public function colorClass(): string
    {
        return match($this) {
            self::Red => 'text-red-500',
            self::Blue => 'text-blue-500',
            self::Green => 'text-green-500',
            self::Orange => 'text-orange-500',
            self::Yellow => 'text-yellow-500',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
