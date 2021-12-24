<?php

namespace App\Tests\Service;

use App\Service\Calculator;
use PHPUnit\Framework\TestCase;

/**
 * Class permettant de valider le fonctionnement du service Calculator
 */
class CalculatorTest extends TestCase {
    /**
     * Méthode permettant de tester la fonction add du serice Calculator
     *
     * @return void
     */
    public function testAdd() {
        $calculator = new Calculator();

        // $result doit contenir l'addition de 1 et 3, à savoir 4
        // $result = 4
        $result = $calculator->add(1, 3);
        // assertEquals : est-ce que 4 est égal au résultat envoyé par le service
        $this->assertEquals(4, $result, "Le service calculator ne fonctionne pas comme prévu ${result} au lieu de 4");
    }
    

    public function testMultiply() {
        $calculator = new Calculator();

        // $result doit contenir l'addition de 1 et 3, à savoir 4
        // $result = 6
        $result = $calculator->multiply(2, 3);

        $this->assertEquals(6, $result, "Le service calculator ne fonctionne pas comme prévu ${result} au lieu de 6");
    }
}