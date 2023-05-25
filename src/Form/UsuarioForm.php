<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UsuarioForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add("id", HiddenType::class);
        $builder->add("username", TextType::class, [
            "label" => "Nombre de usuario",
            "attr" => [
                "col" => "s12", 
                "icon" => "face"
            ]
        ]);
        $builder->add("correo", EmailType::class, [
            "label" => "correo electrónico",
            "attr" => [
                "col" => "s12",
                "icon" => "mail"
            ]
            ]);
    }
}