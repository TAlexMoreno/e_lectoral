<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class Index extends AbstractController {
    #[Route("/", name: "app_index")]
    public function index(){
        $this->denyAccessUnlessGranted("ROLE_USER");
    }
}