<?php

namespace App\Tests\Service;

use App\Service\Slugger;
use PHPUnit\Framework\TestCase;


class SluggerTest extends TestCase {

    public function testSlugger()
    {
        $slugger = new Slugger('en');

        $result = $slugger->slugify('Breaking bad');

        $this->assertEquals('breaking-bad', $result, "Le service Slugger ne fonctionne pas comme prévu. {$result} au lieu de breaking-bad");
    }
}