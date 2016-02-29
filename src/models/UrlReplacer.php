<?php

namespace src\models;

class UrlReplacer
{
    public function replaceUrls(Botan $botan, $message, $userID)
    {
        preg_match_all('/\[.+?\]\((.+?)\)/', $message, $matches);

        if (!array_key_exists(1, $matches) || empty($matches[1])) {
            return $message;
        }

        $urls = array_flip($matches[1]);

        foreach ($urls as $originalURL => &$value) {
            $value = $botan->shortenUrl($originalURL, $userID);
        }
        
        return strtr($message, $urls);
    }
}