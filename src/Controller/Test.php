<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Test extends AbstractController {
    #[Route("/test/mapa", name: "test_mapa")]
    public function testMapa(){
        return $this->render('test/mapa.html.twig');
    }

    #[Route("/test/card", name: "test_card")]
    public function card(){
        return $this->render("test/card.html.twig");
    }
}