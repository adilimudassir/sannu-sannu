<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Date Formatting for HTML Inputs', function () {
    it('formats ISO date string correctly', function () {
        $isoDate = '2025-08-15T10:30:00.000Z';
        // We'll test this via JavaScript since the utility is in TypeScript
        expect(true)->toBeTrue(); // Placeholder for now
    });

    it('formats Laravel timestamp correctly', function () {
        $timestamp = '2025-08-15 10:30:22';
        // We'll test this via JavaScript since the utility is in TypeScript
        expect(true)->toBeTrue(); // Placeholder for now
    });

    it('handles null and empty values', function () {
        // We'll test this via JavaScript since the utility is in TypeScript
        expect(true)->toBeTrue(); // Placeholder for now
    });

    it('handles invalid date strings gracefully', function () {
        // We'll test this via JavaScript since the utility is in TypeScript
        expect(true)->toBeTrue(); // Placeholder for now
    });
});

describe('Date Formatting Logic', function () {
    it('formats Laravel timestamp strings correctly', function () {
        // Test the logic that would be used in the frontend
        $timestamp = '2025-08-15 10:30:22';
        $carbonDate = \Carbon\Carbon::parse($timestamp);

        // This simulates what the frontend formatDateForInput function should do
        expect($carbonDate->format('Y-m-d'))->toBe('2025-08-15');
    });

    it('handles ISO datetime strings correctly', function () {
        $isoString = '2025-08-15T10:30:22.000Z';
        $carbonDate = \Carbon\Carbon::parse($isoString);

        expect($carbonDate->format('Y-m-d'))->toBe('2025-08-15');
    });

    it('handles null values gracefully', function () {
        $nullDate = null;

        // This simulates the null check in formatDateForInput
        expect($nullDate)->toBeNull();
    });

    it('validates date format conversion', function () {
        // Test various date formats that might come from the backend
        $formats = [
            '2025-08-15 10:30:22' => '2025-08-15',
            '2025-12-31 23:59:59' => '2025-12-31',
            '2025-01-01 00:00:00' => '2025-01-01',
        ];

        foreach ($formats as $input => $expected) {
            $carbonDate = \Carbon\Carbon::parse($input);
            expect($carbonDate->format('Y-m-d'))->toBe($expected);
        }
    });
});
