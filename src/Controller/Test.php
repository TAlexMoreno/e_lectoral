<?php

namespace App\Controller;

use App\Enums\GeoJSONLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;

class Test extends AbstractController {
    #[Route("/test/mapa", name: "test_mapa")]
    public function testMapa(){
        return $this->render('test/mapa.html.twig');
    }

    #[Route("/test/card", name: "test_card")]
    public function card(){
        return $this->render("test/card.html.twig");
    }

    #[Route("/test/geojson/{nivel}/{id}", name: "geojson", requirements: ["nivel" => new EnumRequirement(GeoJSONLevel::class)])]
    public function geojson(GeoJSONLevel $nivel, int $id, KernelInterface $kernel){
        $fs = new Filesystem();
        $geojsonDir = $kernel->getProjectDir() . "/files/geojson/" . $nivel->name;
        $geojson = $geojsonDir."/".str_pad($id, 2, "0", STR_PAD_LEFT).".geojson";
        if (!$fs->exists($geojson)) $this->createNotFoundException("El archivo solicitado no existe");
        return new BinaryFileResponse($geojson);
    }
}