<?php

namespace App\Form;

use App\Entity\Note;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EditNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            "label" => "Title",
            "attr" => ["class" => "form-control", "placeholder" => "title","id" => "title"]
        ])
        ->add('description', TextareaType::class, [
            "label" => "Description",
            "attr" => ["class" => "form-control ", "placeholder" => "description","id"=>"description"]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
