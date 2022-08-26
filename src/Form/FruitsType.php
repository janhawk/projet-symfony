<?php

namespace App\Form;

use App\Entity\Fruits;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FruitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'attr' => [    
                'maxLength' => 100
            ]
        ])
        ->add('poids', NumberType::class, [
            'attr' => [
                'min' => 0,
                'max' => 9999.99,
                'step' => 0.01
            ]
        ])
        ->add('price', NumberType::class, [
            'attr' => [
                'min' => 0,
                'max' => 9999.99,
                'step' => 0.01
            ]
        ])
        ->add('description', TextareaType::class, [
            'attr' => [
                'maxLength' => 255
            ]
        ])
        // ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fruits::class,
        ]);
    }
}
