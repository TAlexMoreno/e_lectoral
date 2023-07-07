<?php
namespace App\Controller;

use App\Form\PromotorForm;
use App\Repository\PromotorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Promotores extends AbstractController {

    #[Route("/promotores", name: "app_promotores")]
    public function promotores(){
        $this->denyAccessUnlessGranted("ROLE_PROMOTOR_II");
        return $this->render("promotores/promotores.twig");
    }
    #[Route("/promotores/nuevo", name: "promotores_nuevo", methods: ["GET"])]
    public function promotoresNuevo(){
        $this->denyAccessUnlessGranted("ROLE_PROMOTOR_II");
        $form = $this->createForm(PromotorForm::class, options: ["csrf_token_id" => "new_promotor"]);
        return $this->render("promotores/nuevoPromotor.twig", [
            "form" => $form->createView()
        ]);
    }
    #[Route("/promotores/nuevo", name: "promotores_nuevo", methods: ["POST"])]
    public function promotoresSave(Request $req, PromotorRepository $prepo){
        // TODO: implementar save
        // $check = $prepo->findOneBy(["correo" => $req->get("promotor_form")["correo"]]);
        // $update = !empty($req->get("promotor_form")["id"]);
        // if ($check && !$update) 
    }
}