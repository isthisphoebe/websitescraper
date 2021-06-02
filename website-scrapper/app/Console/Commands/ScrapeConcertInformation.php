<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Response;

class ScrapeConcertInformation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:concertinformation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Concert Information Scraper';

    // protected $collections = [
    //     'artists',
    //     'city',
    //     'venue',
    //     'date',
    //     'price',
    //     ];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        ini_set('max_execution_time', '700');


        // Call to website -> Grab pagination
        $crawler = \Goutte::request('GET', env('SCRAPE_URL').'all');

        $pages = ($crawler->filter('#paginate a')->count() > 0)
        ? $crawler->filter('#paginate a:nth-last-child(2)')->text()
        : 0;

        $eventinfo = array();

        // For each page grab all title links

        // for($i = 0; $i < $pages; $i++){
            for($i = 0; $i < 5; $i++){

        $crawler = \Goutte::request('GET', env('SCRAPE_URL').'page/'.$i.'/all/');

        $links = $crawler->filter('.event_link')->each(function($node) {
            return $node->attr('href');
        });

        $linkcount = count($links);

        // With each title link, go to page and grab all event data

        for($a = 0; $a < $linkcount; $a++){

            $crawler = \Goutte::request('GET', $links[$a]);

            $event = array();

            $artistcount = $crawler->filter('.left.full-width-mobile.event-information.event-width h1')->count();
                if($artistcount > 0){
                    $event['artist'] = $crawler->filter('.left.full-width-mobile.event-information.event-width h1')->text();
            }

            $venuecount = $crawler->filter('.venue-details a')->count();
            if($venuecount > 0){
                $event['venue'] = $crawler->filter('.venue-details a')->text();
            }

            $citycount = $crawler->filter('.EventLocation .secondaryInformation')->count();
            if($citycount > 0){
                $event['city'] = $crawler->filter('.EventLocation .secondaryInformation')->text();
            }

            $datecount = $crawler->filter('.venue-details td')->count();
            if($datecount >= 3){
                $event['date'] = $crawler->filter('.venue-details td')->eq(3)->text();
            }

            $pricecount = $crawler->filter('.price')->count();
            if($pricecount > 0){
                $event['price'] = $crawler->filter('.price')->text();
            }

            $eventinfo[$crawler->filter('.left.full-width-mobile.event-information.event-width h1')->text()] = $event;

        }

        }

        dd($eventinfo);


    }
}
