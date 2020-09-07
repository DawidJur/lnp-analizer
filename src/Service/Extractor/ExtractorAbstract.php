<?php


namespace App\Service\Extractor;


abstract class ExtractorAbstract
{
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }


    protected function getWebsiteContent(string $url): string
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($this->curl);
    }
}