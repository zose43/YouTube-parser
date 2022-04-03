<?php

namespace App\Console\Storages;

use Google_Client;
use Google\Exception;
use Google_Service_Sheets;
use Illuminate\Support\Collection;
use Google_Service_Sheets_ValueRange;
use App\Exceptions\CredentialsException;
use Google_Service_Sheets_ClearValuesRequest;

class GoogleStorage extends Storage
{
    private const CREDENTIALS = __DIR__ . '/credentials.json';

    private Google_Service_Sheets $service;
    private Google_Service_Sheets_ClearValuesRequest $clear;

    /**
     * @throws CredentialsException
     */
    public function __construct( private string $spreadsheetId,
        private string $rangeId )
    {
        $client = new Google_Client();
        $client->setApplicationName( 'you_tube_parser' );
        $client->setScopes( Google_Service_Sheets::SPREADSHEETS );
        $client->setAccessType( 'offline' );

        if ( !file_exists( self::CREDENTIALS ) ) {
            throw new CredentialsException();
        }
        try {
            $client->setAuthConfig( self::CREDENTIALS );
        }
        catch ( Exception $e ) {
            die( $e->__toString() );
        }

        $this->service = new Google_Service_Sheets( $client );
        $this->clear = new Google_Service_Sheets_ClearValuesRequest();
        $this->cleanAll();
    }

    protected function cleanAll(): void
    {
        $this->service->spreadsheets_values->clear( $this->spreadsheetId, $this->rangeId, $this->clear );
    }

    public function add( array $data ): int
    {
        $body = new Google_Service_Sheets_ValueRange( [ 'values' => $data ] );
        $this->service->spreadsheets_values->update( $this->spreadsheetId, $this->rangeId, $body, [ 'valueInputOption' => 'USER_ENTERED' ] );

        return $body->count() - 2;
    }

    public function getTableInfo(): Collection
    {
        $tableInfo = Collection::make();
        $tableInfo->title = $this->service->spreadsheets->get( $this->spreadsheetId )->getProperties()->getTitle();
        $tableInfo->locale = $this->service->spreadsheets->get( $this->spreadsheetId )->getProperties()->getLocale();
        $tableInfo->url = $this->service->spreadsheets->get( $this->spreadsheetId )->getSpreadsheetUrl();

        return $tableInfo;
    }
}