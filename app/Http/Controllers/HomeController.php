<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {


        // $response = Http::get('https://gozayaan.com/flight/list?adult=1&child=0&child_age=&infant=0&cabin_class=Economy&trips=DAC,CXB,2026-03-27');

        // return $html = $response->body();

        // $crawler = new Crawler($html);

        // $crawler->filter('.flight-card-container')->each(function ($node) {
        //     $airline = $node->filter('.airline-name')->text();
        //     $price = $node->filter('.price-text')->text();

        //     dump($airline, $price);

        //});
        return view('home');
    }


}