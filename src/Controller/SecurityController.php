<?php

namespace App\Controller;

use App\Enums\EstadosUsuario;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response{
        /** @var App\Entity\Usuario $user */
        $user = $this->getUser();
        if ($user) return $user->getEstatus == EstadosUsuario::Operative ? $this->redirectToRoute('app_index') : $this->redirect("change_pass");
        
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route("/changePass", name: "change_pass", methods:["GET"])]
    public function changePass(){
        return $this->render("security/changePass.html.twig");
    }
    
    #[Route("/changePass", name: "change_pass_post", methods:["POST"])]
    public function changePassPost(Request $req, UsuarioRepository $urepo, UserPasswordHasherInterface $hasher){
        $p1 = $req->get("p1");
        $p2 = $req->get("p2");

        $uppercase    = preg_match('@[A-Z]@', $p1);
        $lowercase    = preg_match('@[a-z]@', $p1);
        $number       = preg_match('@[0-9]@', $p1);
        $specialchars = preg_match('@[^\w]@', $p1);

        if (!$uppercase || !$lowercase || !$number || !$specialchars || strlen($p1) < 8){
            return $this->json(["error" => "La contraseña no cumple con los requisitos de seguridad", "errno" => 1], Response::HTTP_BAD_REQUEST);
        }

        if ($p1 !== $p2){
            return $this->json(["error" => "Las contraseñas no coinciden", "errno" => 2], Response::HTTP_BAD_REQUEST);
        }

        /** @var \App\Entity\Usuario $user */
        $user = $this->getUser();
        $user->setPassword($hasher->hashPassword($user, $p1));
        $user->setEstatus(EstadosUsuario::Operative);
        $urepo->save($user, true);

        return $this->json(["success" => true, "action" => "redirect", "path" => $this->generateUrl("app_index")]);
    }
}
