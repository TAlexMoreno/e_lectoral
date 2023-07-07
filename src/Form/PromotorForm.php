<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Promotor;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PromotorForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options){
        /** @var ?Promotor $promotor */
        $promotor = $builder->getData();
        $builder->add("id", HiddenType::class, [
            "mapped" => false,
            "data" => $promotor?->getId() ?? ""
        ]);
        $builder->add("nombre", TextType::class, [
            "attr" => ["col" => "s12 m4", "icon" => "face"],
        ]);
        $builder->add("apellidoPaterno", TextType::class, [
            "attr" => ["col" => "s12 m4"]
        ]);
        $builder->add("apellidoMaterno", TextType::class, [
            "attr" => ["col" => "s12 m4"]
        ]);
        $builder->add("telefono", TextType::class, [
            "attr" => ["col" => "s12 m6", "icon" => "call"],
        ]);
        $builder->add("correo", EmailType::class, [
            "mapped" => false,
            "attr" => ["col" => "s12 m6", "icon" => "mail"],
        ]);
    }
}