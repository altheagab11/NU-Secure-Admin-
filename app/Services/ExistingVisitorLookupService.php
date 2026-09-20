<?php

namespace App\Services;

class ExistingVisitorLookupService
{
    public const STATUS_NO_MATCH = 'no_match';

    public const STATUS_SINGLE_MATCH = 'single_match';

    public const STATUS_MULTIPLE_MATCHES = 'multiple_matches';

    /**
     * Normalize a visitor name for Name + Birthday matching.
     *
     * Collapses whitespace and ignores letter case so these are equivalent:
     * "JOCELYN HERNANDEZ", "Jocelyn Hernandez", "  Jocelyn   Hernandez  "
     */
    public function normalizeFullName(string $firstName, string $lastName = ''): string
    {
        $combined = trim($firstName.' '.$lastName);
        $combined = preg_replace('/\s+/u', ' ', $combined) ?? $combined;

        return strtolower(trim($combined));
    }

    public function namesMatch(string $firstNameA, string $lastNameA, string $firstNameB, string $lastNameB): bool
    {
        $left = $this->normalizeFullName($firstNameA, $lastNameA);
        $right = $this->normalizeFullName($firstNameB, $lastNameB);

        return $left !== '' && $left === $right;
    }

    public function identityMatches(
        string $firstNameA,
        string $lastNameA,
        ?string $birthdayA,
        string $firstNameB,
        string $lastNameB,
        ?string $birthdayB
    ): bool {
        if ($birthdayA === null || $birthdayB === null || $birthdayA === '' || $birthdayB === '') {
            return false;
        }

        return $this->namesMatch($firstNameA, $lastNameA, $firstNameB, $lastNameB)
            && $birthdayA === $birthdayB;
    }

    /**
     * Mask a contact number for multi-match display.
     * Example: 09952604071 → 0995*****71
     */
    public function maskContactNumber(?string $contact): string
    {
        $digits = preg_replace('/\D+/', '', (string) $contact) ?? '';

        if ($digits === '') {
            return '-';
        }

        $length = strlen($digits);
        if ($length < 6) {
            return str_repeat('*', $length);
        }

        $maskLength = $length - 6;

        return substr($digits, 0, 4).str_repeat('*', $maskLength).substr($digits, -2);
    }

    public function classifyMatchCount(int $count): string
    {
        if ($count <= 0) {
            return self::STATUS_NO_MATCH;
        }

        if ($count === 1) {
            return self::STATUS_SINGLE_MATCH;
        }

        return self::STATUS_MULTIPLE_MATCHES;
    }

    /**
     * PostgreSQL expression that normalizes visitor.first_name + last_name
     * the same way as normalizeFullName().
     */
    public function normalizedFullNameExpression(?string $alias = null): string
    {
        $first = $alias ? $alias.'.first_name' : 'first_name';
        $last = $alias ? $alias.'.last_name' : 'last_name';

        return "regexp_replace(lower(trim(concat_ws(' ', coalesce({$first}, ''), coalesce({$last}, '')))), '\\s+', ' ', 'g')";
    }

    /**
     * Build the lookup API payload. A single match keeps the current
     * top-level visitor fields so the existing modal can keep working.
     *
     * @param  list<array<string, mixed>>  $visitors
     * @return array<string, mixed>
     */
    public function buildLookupPayload(array $visitors): array
    {
        $count = count($visitors);
        $status = $this->classifyMatchCount($count);

        if ($count === 0) {
            return [
                'exists' => false,
                'status' => self::STATUS_NO_MATCH,
                'match_count' => 0,
                'visitors' => [],
            ];
        }

        $payload = [
            'exists' => true,
            'status' => $status,
            'match_count' => $count,
            'visitors' => $visitors,
        ];

        if ($count === 1) {
            return array_merge($visitors[0], $payload);
        }

        return $payload;
    }
}
