<?php
namespace App\Controller;

use App\Entity\Ruta;
use App\Repository\RutaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Index extends AbstractController {
    #[Route("/", name: "app_index")]
    public function index(){
        $this->denyAccessUnlessGranted("ROLE_USER");
        return $this->render("main/index.html.twig");
    }

    #[Route("/getRutas", name: "get_rutas")]
    public function getRutas(RutaRepository $rrepo){
        $rutas = $rrepo->findAll();
        $rutas = array_filter($rutas, function(Ruta $ruta) {
            return $this->isGranted($ruta->getMinimumRole());
        });
        return $this->json(["success" => true, "rutas" => $rutas]);
    }
}