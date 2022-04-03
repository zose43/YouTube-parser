<?php

namespace App\Console\Parsers;

use App\Exceptions\HttpException;
use App\Exceptions\JsonProcessException;
use GuzzleHttp\Exception\GuzzleException;
use App\Exceptions\WrongArrayDataException;

class HttpParser extends Parser
{
    private const TABLE_KEYS = [ 'URL', 'Title', 'Description' ];
    private const URL_SEARCH = 'https://www.youtube.com/results?search_query=';
    private const URL_VIDEO = 'https://www.youtube.com/watch?v=';

    private string $word;

    /**
     * @throws WrongArrayDataException|JsonProcessException
     */
    private function sortMatchData( array $data ): array
    {
        $clearData = [];
        $count = count( $data[ 'URL' ] );
        $counter = 1;

        for ( $i = 0; $i < $count; ++$i ) {
            foreach ( self::TABLE_KEYS as $key ) {
                if ( empty( $data[ $key ][ $i ] ) ) {
                    throw new WrongArrayDataException();
                }

                if ( $key !== 'URL' ) {
                    if ( empty( $json = json_decode( $data[ $key ][ $i ], false, 512 ) ) ) {
                        throw new JsonProcessException( json: [ $json, $key, $i, $data[ $key ][ $i ] ] );
                    }

                    $clearData[ $i ][] = $json->text;
                }
                else {
                    $clearData[ $i ][] = self::URL_VIDEO . str_replace( '"', '', $data[ $key ][ $i ] );
                }
            }

            $clearData[ $i ][] = $counter;
            $counter++;
        }

        array_unshift( $clearData, [
            'Поисковый запрос',
            $this->word,
        ], explode( ',', implode( ',', self::TABLE_KEYS ) . ',Position' ) );

        return $clearData;
    }

    protected function filterData( string $html ): array
    {
        preg_match_all( '/"videoRenderer":\s*{\s*"videoId":\s*(?<URL>.*?),.*?"title":\s*{\s*"runs":\s*\[(?<Title>.*?)],.*?"snippetText":\s*{"runs":\s*\[(?<Description>.*?)(]}|,{)/s', $html, $matchData );

        try {
            return $this->sortMatchData( $matchData );
        }
        catch ( JsonProcessException $e ) {
            die( $e->getMessage() . PHP_EOL . implode( '-', $e->getWrongJson() ) );
        }
        catch ( WrongArrayDataException $e ) {
            die( $e->__toString() );
        }
    }

    /**
     * @throws HttpException|GuzzleException
     */
    public function getData( string $word ): array
    {
        $this->word = $word;
        $http = $this->getClient();
        $url = self::URL_SEARCH . $word;

        if ( $http->get( $url )->getStatusCode() >= 400 ) {
            throw new HttpException( $url );
        }

        $ytData = $http->get( $url )->getBody()->getContents();

        return $this->filterData( $ytData );
    }
}