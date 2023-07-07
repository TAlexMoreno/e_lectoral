<?php
namespace App\Form;

use App\Entity\Partido;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function PHPSTORM_META\map;

class UsuarioForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options){
        $user = $builder->getData();
        $builder->add("id", HiddenType::class, [
            "mapped" => false,
            "data" => $user?->getId() ?? ""
        ]);
        $builder->add("roles", ChoiceType::class, [
            "label" => "Tipo de usuario",
            "attr" => ["col" => "s12", "icon" => "hdr_auto"],
            "choices" => [
                "Seleccione una opcion" => null,
                "Admin" => "ROLE_ADMIN",
                "Coordinador" => "ROLE_COORDINADOR",
            ],
            "choice_attr" => function($a){
                return !$a ? ["disabled" => true] : [];
            },
            "getter" => function(Usuario $user) {
                return $user->getRoles()[0];
            },
            "setter" => function (Usuario &$user, ?string $role){
                $user->setRoles([$role]);
            }
        ]);
        $builder->add("username", TextType::class, [
            "label" => "Nombre de usuario",
            "attr" => [
                "col" => "s12", 
                "icon" => "face",
                "pattern" => "[a-zA-z0-9]{6,12}",
                "class" => "validate"
            ]
        ]);
        $builder->add("correo", EmailType::class, [
            "label" => "correo electrónico",
            "attr" => [
                "col" => "s12",
                "icon" => "mail",
                "class" => "validate"
            ]
        ]);

        if ($user) {
            $builder->add("Partido", EntityType::class, [
                "class" => Partido::class,
                "placeholder" => "Seleccione una opción",
                "empty_data" => null,
                "choice_label" => function(Partido $partido){
                    return "[{$partido->getSiglas()}] {$partido->getNombre()}";
                },
                "choice_attr" => function(?Partido $partido){
                    if (!$partido) dd($partido);
                    return [];
                }
            ]);
        }
               
    }
}