<?php
namespace App\Command;

use App\Entity\Ruta;
use App\Entity\Usuario;
use App\Enums\EstadosUsuario;
use App\Misc\Utils;
use App\Repository\RutaRepository;
use App\Repository\UsuarioRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Install extends Command {
    public function __construct(private UsuarioRepository $urepo, private RutaRepository $rrepo, private UserPasswordHasherInterface $hasher){
        parent::__construct();
    }
    protected function configure(){
        $this->setName("app:install");
        $this->addArgument("correo", InputArgument::REQUIRED, "Correo electrónico del administrador");
        $this->setDescription("Instala las dependencias, e inyecta información de inicio a la base de datos");
    }
    protected function execute(InputInterface $input, OutputInterface $output){
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper("question");
        $output->writeln("Verificando si existe usuario ROOT");
        $admin = $this->urepo->findOneBy(["username" => "root"]);
        if (!$admin){
            $admin = new Usuario();
            $admin->setUsername("root");
            $admin->setEstatus(EstadosUsuario::JustCreated);
            $rawPass = Utils::randomString(6);
            $admin->setPassword($this->hasher->hashPassword($admin, $rawPass));
            $admin->setRoles(["ROLE_ROOT"]);
            $admin->setCorreo($input->getArgument("correo"));
            $admin->setCreatedAt(new DateTimeImmutable());
            $this->urepo->save($admin, true);
            $output->writeln("<info>Administrador creado satisfactoriamente ingresar con el siguiente password:</info> $rawPass");
        }else {
            //TODO: implementar pregunta y reseteo de contraseña de admin
        }
        $output->writeln("Instalando rutas...");
        $rutas = $this->getRutas();
        foreach ($rutas as $ruta) {
            $old = $this->rrepo->find($ruta->getId());
            if ($old){
                $this->rrepo->remove($old, true);
                $output->write("Reemplazando {$old->getLabel()} con ");
            }
            $this->rrepo->save($ruta, true);
            $output->write($ruta->getLabel()."\n");
        }
        $output->writeln("<info>Rutas instaladas</info>");

        return Command::SUCCESS;
    }

    private function getRutas(): array{
        return [
            new Ruta(1, "/", "Inicio", "home", 0, "ROLE_USER"),
            new Ruta(2, "/admin/usuarios", "Usuarios", "group", 10, "ROLE_ADMIN"),
            new Ruta(3, "/admin/partidos", "Partidos", "flag", "20", "ROLE_ADMIN"),
            new Ruta(4, "/promotores", "Promotores", "book", "30", "ROLE_PROMOTOR_III"),
            new Ruta(5, "/logout", "Cerrar sesión", "logout", -1, "ROLE_USER")
        ];
    }
}