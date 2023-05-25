<?php
namespace App\Controller;

use App\Form\UsuarioForm;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class Admin extends AbstractController {
    #[Route("/admin/usuarios", name: "admin_usuarios")]
    public function adminUsuarios(){
        return $this->render("admin/usuarios.html.twig");
    }
    #[Route("/admin/usuarios/nuevo", name: "usuarios_nuevo")]
    public function usuariosNuevo(){
        $formView = $this->createForm(UsuarioForm::class, null)->createView();
        return $this->render("admin/nuevoUsuario.html.twig", [
            "form" => $formView
        ]);
    }
    #[Route("/admin/usuarios/{username}", name: "admin_usuario")]
    public function adminUsuario(string $username, UsuarioRepository $urepo){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $user = $urepo->createQueryBuilder("u")
            ->where("u.username = :username")
            ->setParameter("username", $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$user) throw new NotFoundHttpException("El usuario $username no existe");

        return $this->render("admin/usuario.html.twig", [
            "user" => $user,
        ]);
    }
}