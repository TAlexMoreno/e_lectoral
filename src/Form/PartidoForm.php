<?php
namespace App\Form;

use App\Entity\Partido;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PartidoForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->add("id", HiddenType::class, [
            "mapped" => false,
            "data" => $builder->getData()?->getId()
        ]);
        $builder->add("nombre", TextType::class);
        $builder->add("siglas", TextType::class, [
            "attr" => ["pattern" => "[a-zA-Z0-9]{2,}"]
        ]);
        $builder->add("imagen", FileType::class, [
            "mapped" => true,
            "getter" => function(Partido $partido){
                return $partido->getUploadedFile();
            },
            "setter" => function(Partido &$partido, ?UploadedFile $file){
                $partido->setUploadedFile($file);
            }
        ]);
        $builder->add("color", TextType::class, [
            "required" => false,
            "attr" => ["pattern" => "^(#[0-9a-fA-F]{3}|#[0-9a-fA-F]{6}|#[0-9a-fA-F]{8})$"]
        ]);
    }
}