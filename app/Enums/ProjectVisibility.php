<?php

namespace App\Enums;

enum ProjectVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case INVITE_ONLY = 'invite_only';

    /**
     * Get all available visibility options
     */
    public static function all(): array
    {
        return [
            self::PUBLIC,
            self::PRIVATE,
            self::INVITE_ONLY,
        ];
    }

    /**
     * Get visibility options that allow public discovery
     */
    public static function publiclyDiscoverable(): array
    {
        return [
            self::PUBLIC,
        ];
    }

    /**
     * Get visibility options that require membership or invitation
     */
    public static function restrictedAccess(): array
    {
        return [
            self::PRIVATE,
            self::INVITE_ONLY,
        ];
    }

    /**
     * Check if this visibility allows public discovery
     */
    public function isPubliclyDiscoverable(): bool
    {
        return in_array($this, self::publiclyDiscoverable());
    }

    /**
     * Check if this visibility requires membership or invitation
     */
    public function hasRestrictedAccess(): bool
    {
        return in_array($this, self::restrictedAccess());
    }

    /**
     * Get the display label for the visibility
     */
    public function label(): string
    {
        return match($this) {
            self::PUBLIC => 'Public',
            self::PRIVATE => 'Private',
            self::INVITE_ONLY => 'Invite Only',
        };
    }

    /**
     * Get the description for the visibility option
     */
    public function description(): string
    {
        return match($this) {
            self::PUBLIC => 'Anyone can view and join this project',
            self::PRIVATE => 'Only tenant members can view and join this project',
            self::INVITE_ONLY => 'Only invited users can view and join this project',
        };
    }

    /**
     * Get the icon class for the visibility option
     */
    public function iconClass(): string
    {
        return match($this) {
            self::PUBLIC => 'globe',
            self::PRIVATE => 'lock',
            self::INVITE_ONLY => 'user-plus',
        };
    }
}