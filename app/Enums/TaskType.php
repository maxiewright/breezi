<?php

namespace App\Enums;

enum TaskType: string
{
    case Install = 'install';
    case Service = 'service';
    case Repair = 'repair';
    case Maintenance = 'maintenance';
    case Inspection = 'inspection';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Install => 'Install',
            self::Service => 'Service',
            self::Repair => 'Repair',
            self::Maintenance => 'Maintenance',
            self::Inspection => 'Inspection',
            self::Other => 'Other',
        };
    }
}
