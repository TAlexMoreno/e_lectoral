<?php
namespace App\Controller;

use App\Entity\Partido;
use App\Entity\Usuario;
use App\Enums\EstadosUsuario;
use App\Form\PartidoForm;
use App\Form\UsuarioForm;
use App\Repository\PartidoRepository;
use App\Repository\UsuarioRepository;
use App\Utils\StringTools;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Admin extends AbstractController {
    #[Route("/admin/usuarios", name: "admin_usuarios")]
    public function adminUsuarios(){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render("admin/usuarios.html.twig");
    }
    #[Route("/admin/usuarios/nuevo", name: "usuarios_nuevo", methods:["GET"])]
    public function usuariosNuevo(){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $formView = $this->createForm(UsuarioForm::class, options: ["csrf_token_id" => "new_usuario"])->createView();
        return $this->render("admin/nuevoUsuario.html.twig", [
            "form" => $formView
        ]);
    }
    #[Route("/admin/usuarios/nuevo", name: "usaurios_save", methods: ["POST"])]
    public function usuarioSave(Request $req, UsuarioRepository $urepo, LoggerInterface $logger, UserPasswordHasherInterface $hasher){
        $check = $urepo->findOneBy(["username" => $req->get("usuario_form")["username"]]);
        $update = !empty($req->get("usuario_form")["id"]);
        if ($check && !$update) return $this->json(["success" => false, "error" => "El nombre de usuario ya existe", "errno" => "EU001"]);
        $form = $this->createForm(UsuarioForm::class, $update ? $check : new Usuario(), ["csrf_token_id" => $update ? "update_usuario" : "new_usuario"]);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()){
            /** @var Usuario $usuario */
            $usuario = $form->getData();
            $usuario->setEstatus(EstadosUsuario::JustCreated);
            $usuario->setCreatedAt(new DateTimeImmutable());
            $plainPass = StringTools::randomString("8");
            $usuario->setPassword($hasher->hashPassword($usuario, $plainPass));
            $logger->debug("'id' => {$usuario->getId()}, 'username' => {$usuario->getUsername()}, 'plainPass' => {$plainPass}]", ["reason" => "user creation"]);
            $urepo->save($usuario, true);
            switch ($usuario->getRoles()[0]) {
                case 'ROLE_COORDINADOR':
                    $url = $this->generateUrl("admin_usuario", ["username" => $usuario->getUsername()]);
                    break;
                
                default:
                    $url = $this->generateUrl("admin_usuarios");
                    break;
            }
            return $this->json(["success" => true, "action" => "redirect", "path" => $url]);
        }else {
            //TODO: handle error
            foreach ($form->getErrors(true) as $error) {
                dd($error);
            }
            return $this->json(["success" => false, "error" => "To implement"]);
        }
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
        $userForm = $this->createForm(UsuarioForm::class, $user, ["csrf_token_id" => "update_usuario"]);

        return $this->render("admin/usuario.html.twig", [
            "user" => $user,
            "form" => $userForm->createView()
        ]);
    }
    #[Route("/admin/usuarios/{username}/foto", name: "foto_perfil")]
    public function fotoPerfil(string $username, UsuarioRepository $urepo, KernelInterface $kernel){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $user = $urepo->createQueryBuilder("u")
            ->where("u.username = :username")
            ->setParameter("username", $username)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$user) throw new NotFoundHttpException("El usuario $username no existe");
        $finder = new Finder();
        $finder->in($kernel->getProjectDir()."/files/img/usuarios")->files();
        $pic = null;
        foreach ($finder as $file) {
            if (strpos($file->getFilename(), $user->getProfilePicName)) {
                $pic = $file;
                break;
            }
        }
        if (!$pic) return new BinaryFileResponse($kernel->getProjectDir()."/files/img/default_profile.png");
        return new BinaryFileResponse($file);
    }
    #[Route("/admin/partidos", name: "admin_partidos")]
    public function partidos(){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render("admin/partidos.twig");
    }
    #[Route("/admin/partidos/nuevo", name: "partidos_nuevo", methods: ["GET"])]
    public function partidosNuevo(){
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $formView = $this->createForm(PartidoForm::class, options: ["csrf_token_id" => "new_partido"])->createView();
        return $this->render("admin/nuevoPartido.twig", [
            "form" => $formView
        ]);
    }
    #[Route("/admin/partidos/nuevo", name: "partidos_save", methods: ["POST"])]
    public function partidosSave(Request $req, PartidoRepository $prepo, KernelInterface $kernel){
        $check = $prepo->findOneBy(["nombre" => $req->get("partido_form")["nombre"], "siglas" => $req->get("partido_form")["siglas"]]);
        $update = !empty($req->get("partido_form")["id"]);
        if ($check && !$update) return $this->json(["success" => false, "error" => "La combinación de nombre y siglas ya existe", "errno" => "EP001"], Response::HTTP_BAD_REQUEST);
        $form = $this->createForm(PartidoForm::class, $update ? $check : new Partido(), ["csrf_token_id" => $update ? "update_partido" : "new_partido"]);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()){
            /** @var Partido $partido */
            $partido = $form->getData();
            $prepo->save($partido, true);
            $partido->getUploadedFile()?->move($kernel->getProjectDir()."/files/img/partidos", $partido->getFileName());
            return $this->json(["success" => true, "action" => "redirect", "path" => $this->generateUrl("admin_partidos", referenceType: UrlGeneratorInterface::ABSOLUTE_URL)]);
        }else {
            //TODO: handle error
            foreach ($form->getErrors(true) as $error) {
                dd($error);
            }
            return $this->json(["success" => false, "error" => "To implement"]);
        }
    }
    #[Route("/admin/partidos/{id}", name: "admin_partido")]
    public function partido(int $id, PartidoRepository $prepo) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $partido = $prepo->find($id);
        if (!$partido) $this->createNotFoundException("El partido solicitado no existe");
        $form = $this->createForm(PartidoForm::class, $partido, ["csrf_token_id" => "update_partido"])->createView();
        return $this->render("admin/partido.twig", [
            "partido" => $partido,
            "form" => $form
        ]);
    }
    #[Route("/admin/partidos/{id}/foto", name: "partido_foto")]
    public function partidoFoto(int $id, PartidoRepository $prepo, KernelInterface $kernel) {
        $partido = $prepo->find($id);
        $filename = $partido->getFileName($kernel);
        return new BinaryFileResponse($kernel->getProjectDir()."/files/img/partidos/".$filename);
    }
}