<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Get all available statuses
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::ACTIVE,
            self::PAUSED,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }

    /**
     * Get statuses that allow contributions
     */
    public static function acceptingContributions(): array
    {
        return [
            self::ACTIVE,
        ];
    }

    /**
     * Get statuses that are considered active/ongoing
     */
    public static function activeStatuses(): array
    {
        return [
            self::DRAFT,
            self::ACTIVE,
            self::PAUSED,
        ];
    }

    /**
     * Get statuses that are considered final/completed
     */
    public static function finalStatuses(): array
    {
        return [
            self::COMPLETED,
            self::CANCELLED,
        ];
    }

    /**
     * Check if this status allows contributions
     */
    public function acceptsContributions(): bool
    {
        return in_array($this, self::acceptingContributions());
    }

    /**
     * Check if this status is active/ongoing
     */
    public function isActive(): bool
    {
        return in_array($this, self::activeStatuses());
    }

    /**
     * Check if this status is final/completed
     */
    public function isFinal(): bool
    {
        return in_array($this, self::finalStatuses());
    }

    /**
     * Get the display label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get the CSS class for styling the status
     */
    public function cssClass(): string
    {
        return match($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::ACTIVE => 'bg-green-100 text-green-800',
            self::PAUSED => 'bg-yellow-100 text-yellow-800',
            self::COMPLETED => 'bg-blue-100 text-blue-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
        };
    }
}