<?php

namespace App\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class Slugger {

    private $slugger;

    // Mis en commentaire pour faire un test phpunit
    // public function __construct(SluggerInterface $slugger) {
    public function __construct($lang= 'fr') {
        $this->slugger = new AsciiSlugger($lang);
    }
    
    public function slugify($stringValue) {

        // $stringValue = str_replace("'", "-", $stringValue);
        // $stringValue = str_replace(" ", "-", $stringValue);
        // $stringValue = str_replace("é", "e", $stringValue);
        // $stringValue = str_replace("è", "e", $stringValue);
        // $stringValue = str_replace("ê", "e", $stringValue);

        return strtolower($this->slugger->slug($stringValue));
    }
}