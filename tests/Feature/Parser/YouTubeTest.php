<?php

namespace Tests\Feature\Parser;

use Tests\TestCase;
use App\Console\Storages\GoogleStorage;

class YouTubeTest extends TestCase
{
    public function getDataForCheckGoogle(): array
    {
        return [
            [ 'Chris  Luno', true ],
            [ '21 december', true ],
            [ 'CAT', true ],
            [ '21:45', true ],
            [ 'сели-поели', true ],
        ];
    }

    public function testValidCode(): void
    {
        $this->artisan( 'parser:start' )
            ->expectsQuestion( 'Hi, give me your phrase', 'world without war' )
            ->expectsQuestion( 'Hi, give me your table list', 'Лист1' )
            ->expectsQuestion( 'Hi, give me your spreadsheet id', '1TB855RwBrLonLnknKWXnwvxq2NqxfoDdR0Yp-ko0p68' )
            ->assertSuccessful();
    }

    /**
     * @dataProvider getDataForCheckGoogle
     */
    public function testCheckData( string $word, bool $expected ): void
    {
        $spreadsheetId = '1TB855RwBrLonLnknKWXnwvxq2NqxfoDdR0Yp-ko0p68';
        $rangeId = 'Лист1';
        $tableTitles = [ 'URL', 'Title', 'Description', 'Position' ];

        $this->artisan( 'parser:start' )
            ->expectsQuestion( 'Hi, give me your phrase', $word )
            ->expectsQuestion( 'Hi, give me your table list', $rangeId )
            ->expectsQuestion( 'Hi, give me your spreadsheet id', $spreadsheetId );

        $service = GoogleStorage::getService();
        $word = str_replace( ' ', '+', trim( $word ) );
        $firstColumn = [ 'Поисковый запрос', $word ];

        $checkCount = $service->spreadsheets_values->get( $spreadsheetId, $rangeId )->count() > 2;

        $values = $service->spreadsheets_values->get( $spreadsheetId, $rangeId );
        $total = array_merge( array_diff( $firstColumn, $values[ 0 ] ), array_diff( $tableTitles, $values[ 1 ] ) );

        $this->assertEquals( $expected, empty( $total ) && $checkCount );
    }

}