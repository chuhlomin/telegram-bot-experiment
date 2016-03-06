<?php

namespace src\models;

class UrlReplacer
{
    /** @var Botan */
    private $botan;

    public function __construct(Botan $botan)
    {
        $this->botan = $botan;
    }
    
    public function replaceUrls($message, $userID)
    {
        preg_match_all('/\[.+?\]\((.+?)\)/', $message, $matches);

        if (!array_key_exists(1, $matches) || empty($matches[1])) {
            return $message;
        }

        $urls = array_flip($matches[1]);

        foreach ($urls as $originalURL => &$value) {
            $value = $this->botan->shortenUrl($originalURL, $userID);
        }
        
        return strtr($message, $urls);
    }
}
