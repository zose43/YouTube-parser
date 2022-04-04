<?php

namespace App\Console\Commands;

use Exception;
use RuntimeException;
use Illuminate\Console\Command;
use App\Exceptions\HttpException;
use App\Console\Parsers\HttpParser;
use Illuminate\Support\Facades\Log;
use App\Console\Storages\GoogleStorage;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class ParserCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'parser:start';
    protected $description = 'Get data from youtube and save it to google spreadsheet';

    /**
     * @throws Exception
     */
    public function handle( HttpParser $parser ): void
    {
        $word = $this->ask( 'Hi, give me your phrase' );
        $rangeId = $this->ask( 'Hi, give me your table list', 'Лист1' );
        $spreadsheetId = $this->ask( 'Hi, give me your spreadsheet id', '1TB855RwBrLonLnknKWXnwvxq2NqxfoDdR0Yp-ko0p68' );

        $bar = $this->output->createProgressBar( 100 );
        $bar->start();

        if ( preg_replace( '/\s{2,}/', '', $word ) === '' ) {
            Log::channel( 'parsers' )->error( 'Parser Started with empty phrase', [
                'phrase' => $word,
                'rangeId' => $rangeId,
                'spreadsheet id' => $spreadsheetId,
            ] );
            throw new RuntimeException( "Your phrase, table list or spreadsheet id is empty. \n All inputs: [Phrase: $word, Table list: $rangeId, Spreadsheet id: $spreadsheetId]" );
        }

        $word = str_replace( ' ', '+', $word );
        try {
            $loadedData = $parser->getData( $word );
        }
        catch ( GuzzleException $e ) {
            die( $e->__toString() );
        }
        catch ( HttpException $e ) {
            die( $e->getMessage() . PHP_EOL . $e->getInvalidUrl() );
        }

        $bar->advance( 50 );
        $this->output->info( 'Success load data' );

        $googleDriver = new GoogleStorage( $spreadsheetId, $rangeId );
        $addCounts = $googleDriver->add( $loadedData );
        $tableInfo = $googleDriver->getTableInfo();

        $bar->finish();
        Log::channel( 'parsers' )->info( 'Success', [
            'table' => $tableInfo->title,
            'count elements' => $addCounts,
            'url' => $tableInfo->url,
        ] );
        $this->output->info( "Success save data to $tableInfo->title table. All elements: $addCounts. Check table: $tableInfo->url" );
    }

    public function getSubscribedSignals(): array
    {
        return [ SIGINT ];
    }

    public function handleSignal( int $signal ): void
    {
        if ( $signal === SIGINT ) {
            Log::channel( 'parsers' )->alert( 'Process is interrupted' );
            $this->output->error( 'Process is interrupted' );
            exit();
        }
    }
}