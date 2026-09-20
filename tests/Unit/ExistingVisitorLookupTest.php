<?php

namespace Tests\Unit;

use App\Services\ExistingVisitorLookupService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExistingVisitorLookupTest extends TestCase
{
    private ExistingVisitorLookupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExistingVisitorLookupService;
    }

    #[Test]
    public function no_matching_name_and_birthday_is_new_visitor(): void
    {
        $payload = $this->service->buildLookupPayload([]);

        $this->assertFalse($payload['exists']);
        $this->assertSame(ExistingVisitorLookupService::STATUS_NO_MATCH, $payload['status']);
        $this->assertSame(0, $payload['match_count']);
        $this->assertSame([], $payload['visitors']);
    }

    #[Test]
    public function exactly_one_matching_visitor_keeps_the_single_match_payload(): void
    {
        $visitor = $this->visitor(101, 'Jocelyn', 'Hernandez', '1963-10-23', '09952604071');
        $payload = $this->service->buildLookupPayload([$visitor]);

        $this->assertTrue($payload['exists']);
        $this->assertSame(ExistingVisitorLookupService::STATUS_SINGLE_MATCH, $payload['status']);
        $this->assertSame(1, $payload['match_count']);
        $this->assertSame(101, $payload['visitor_id']);
        $this->assertSame(101, $payload['id']);
        $this->assertSame('Jocelyn Hernandez', $payload['full_name']);
        $this->assertCount(1, $payload['visitors']);
        $this->assertSame(101, $payload['visitors'][0]['visitor_id']);
    }

    #[Test]
    public function two_different_visitors_with_the_same_name_and_birthday_are_both_returned(): void
    {
        $visitorA = $this->visitor(101, 'Jocelyn', 'Hernandez', '1963-10-23', '09952604071');
        $visitorB = $this->visitor(205, 'Jocelyn', 'Hernandez', '1963-10-23', '09171234567');
        $payload = $this->service->buildLookupPayload([$visitorA, $visitorB]);

        $this->assertTrue($payload['exists']);
        $this->assertSame(ExistingVisitorLookupService::STATUS_MULTIPLE_MATCHES, $payload['status']);
        $this->assertSame(2, $payload['match_count']);
        $this->assertCount(2, $payload['visitors']);
        $this->assertSame([101, 205], array_column($payload['visitors'], 'visitor_id'));
        $this->assertArrayNotHasKey('visitor_id', $payload);
        $this->assertNotSame(101, $payload['visitors'][1]['visitor_id']);
    }

    #[Test]
    public function three_or_more_matching_visitors_all_appear(): void
    {
        $visitors = [
            $this->visitor(101, 'Jocelyn', 'Hernandez', '1963-10-23', '09952604071'),
            $this->visitor(205, 'Jocelyn', 'Hernandez', '1963-10-23', '09171234567'),
            $this->visitor(309, 'Jocelyn', 'Hernandez', '1963-10-23', '09180000000'),
        ];
        $payload = $this->service->buildLookupPayload($visitors);

        $this->assertSame(ExistingVisitorLookupService::STATUS_MULTIPLE_MATCHES, $payload['status']);
        $this->assertSame(3, $payload['match_count']);
        $this->assertSame([101, 205, 309], array_column($payload['visitors'], 'visitor_id'));
    }

    #[Test]
    public function selected_visitor_id_is_preserved_and_not_replaced_by_the_first_match(): void
    {
        $visitors = [
            $this->visitor(101, 'Jocelyn', 'Hernandez', '1963-10-23', '09952604071'),
            $this->visitor(205, 'Jocelyn', 'Hernandez', '1963-10-23', '09171234567'),
        ];
        $payload = $this->service->buildLookupPayload($visitors);
        $selectedId = 205;

        $selected = collect($payload['visitors'])->firstWhere('visitor_id', $selectedId);

        $this->assertNotNull($selected);
        $this->assertSame(205, $selected['visitor_id']);
        $this->assertSame(205, $selected['id']);
        $this->assertNotSame($payload['visitors'][0]['visitor_id'], $selected['visitor_id']);
    }

    #[Test]
    public function same_name_with_a_different_birthday_is_not_an_exact_match(): void
    {
        $this->assertFalse($this->service->identityMatches(
            'Jocelyn',
            'Hernandez',
            '1963-10-23',
            'Jocelyn',
            'Hernandez',
            '1964-10-23'
        ));
    }

    #[Test]
    public function same_birthday_with_a_different_name_is_not_an_exact_match(): void
    {
        $this->assertFalse($this->service->identityMatches(
            'Jocelyn',
            'Hernandez',
            '1963-10-23',
            'Maria',
            'Hernandez',
            '1963-10-23'
        ));
    }

    #[Test]
    public function the_same_name_matches_after_capitalization_and_extra_spaces_are_normalized(): void
    {
        $this->assertTrue($this->service->namesMatch(
            'JOCELYN',
            'HERNANDEZ',
            'Jocelyn',
            'Hernandez'
        ));
        $this->assertTrue($this->service->namesMatch(
            '  Jocelyn  ',
            '  Hernandez  ',
            'Jocelyn',
            'Hernandez'
        ));
        $this->assertSame(
            'jocelyn hernandez',
            $this->service->normalizeFullName('  Jocelyn   Hernandez  ')
        );
        $this->assertSame(
            'jocelyn hernandez',
            $this->service->normalizeFullName('JOCELYN HERNANDEZ')
        );
        $this->assertTrue($this->service->identityMatches(
            '  Jocelyn   Hernandez  ',
            '',
            '1963-10-23',
            'Jocelyn',
            'Hernandez',
            '1963-10-23'
        ));
    }

    #[Test]
    public function contact_numbers_are_masked_for_multiple_match_display(): void
    {
        $this->assertSame('0995*****71', $this->service->maskContactNumber('09952604071'));
        $this->assertSame('0917*****67', $this->service->maskContactNumber('09171234567'));
        $this->assertSame('0995*****71', $this->service->maskContactNumber('0995-260-4071'));
        $this->assertSame('-', $this->service->maskContactNumber(null));
        $this->assertSame('-', $this->service->maskContactNumber(''));
    }

    #[Test]
    public function match_statuses_follow_zero_one_and_many_counts(): void
    {
        $this->assertSame(ExistingVisitorLookupService::STATUS_NO_MATCH, $this->service->classifyMatchCount(0));
        $this->assertSame(ExistingVisitorLookupService::STATUS_SINGLE_MATCH, $this->service->classifyMatchCount(1));
        $this->assertSame(ExistingVisitorLookupService::STATUS_MULTIPLE_MATCHES, $this->service->classifyMatchCount(2));
        $this->assertSame(ExistingVisitorLookupService::STATUS_MULTIPLE_MATCHES, $this->service->classifyMatchCount(3));
    }

    #[Test]
    public function lookup_query_retrieves_all_matching_visitors_instead_of_the_first_row(): void
    {
        $controllerSource = file_get_contents(app_path('Http/Controllers/GuardVisitorController.php'));
        $this->assertNotFalse($controllerSource);

        $this->assertTrue((bool) preg_match(
            '/protected function findExistingVisitorRecord\(.+?protected function /s',
            $controllerSource,
            $matches
        ));

        $method = $matches[0];
        $this->assertStringContainsString('->get();', $method);
        $this->assertStringNotContainsString('->first();', $method);
        $this->assertStringContainsString('normalizedFullNameExpression', $method);
    }

    /**
     * @return array<string, mixed>
     */
    private function visitor(int $id, string $firstName, string $lastName, string $birthday, string $contact): array
    {
        return [
            'id' => $id,
            'visitor_id' => $id,
            'full_name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthday,
            'birthday' => $birthday,
            'contact_no' => $contact,
            'masked_contact_number' => $this->service->maskContactNumber($contact),
            'validation_photo' => 'photo-'.$id.'.jpg',
        ];
    }
}
