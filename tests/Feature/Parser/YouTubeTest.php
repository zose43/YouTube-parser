<?php

namespace Tests\Feature\Parser;

use Tests\TestCase;
use GuzzleHttp\Client;
use App\Console\Parsers\HttpParser;
use App\Console\Storages\GoogleStorage;

class YouTubeTest extends TestCase
{
    private const RANGE = 'Лист1';
    private const SPREADSHEET = '1TB855RwBrLonLnknKWXnwvxq2NqxfoDdR0Yp-ko0p68';

    private function fixWhiteSpace( string $word ): string
    {
        return str_replace( ' ', '+', trim( $word ) );
    }

    private function checkHttpData( string $title, string $description, int $position, int $count ): bool
    {
        return trim( $title ) !== '' && trim( $description ) !== '' && $position > 0 && $position <= $count;
    }

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
            ->expectsQuestion( 'Hi, give me your table list', self::RANGE )
            ->expectsQuestion( 'Hi, give me your spreadsheet id', self::SPREADSHEET )
            ->assertSuccessful();
    }

    /**
     * @dataProvider getDataForCheckGoogle
     */
    public function testCheckData( string $word, bool $expected ): void
    {
        $tableTitles = [ 'URL', 'Title', 'Description', 'Position' ];

        $this->artisan( 'parser:start' )
            ->expectsQuestion( 'Hi, give me your phrase', $word )
            ->expectsQuestion( 'Hi, give me your table list', self::RANGE )
            ->expectsQuestion( 'Hi, give me your spreadsheet id', self::SPREADSHEET );

        $service = GoogleStorage::getService();
        $word = $this->fixWhiteSpace( $word );
        $firstColumn = [ 'Поисковый запрос', $word ];

        $checkCount = $service->spreadsheets_values->get( self::SPREADSHEET, self::RANGE )->count() > 2;

        $values = $service->spreadsheets_values->get( self::SPREADSHEET, self::RANGE );
        $total = array_merge( array_diff( $firstColumn, $values[ 0 ] ), array_diff( $tableTitles, $values[ 1 ] ) );

        $this->assertEquals( $expected, empty( $total ) && $checkCount );
    }

    /**
     * @dataProvider getDataForCheckGoogle
     */
    public function testHttpData( string $word, bool $expected ): void
    {
        $parser = new HttpParser();
        $client = new Client();
        $data = $parser->getData( $this->fixWhiteSpace( $word ) );
        $result = true;

        foreach ( $data as $key => $items ) {
            if ( $key > 1 ) {
                $response = $client->get( $items[ 0 ] )->getStatusCode() === 200;

                $result = $this->checkHttpData( $items[ 1 ], $items[ 2 ], $items[ 3 ], count( $data ) - 2 );
            }
        }
        $this->assertEquals( $expected, $result && $response );
    }

}