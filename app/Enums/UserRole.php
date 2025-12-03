<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';

    /**
     * Get the label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Operator => 'Operator',
        };
    }

    /**
     * Get badge color for UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Admin => 'emerald',
            self::Operator => 'sky',
        };
    }

    /**
     * Get all roles as array for dropdowns.
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($role) => [
            'value' => $role->value,
            'label' => $role->label(),
        ])->toArray();
    }
}
