<?php

namespace App\Form;

use App\Entity\Partner;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class PartnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name',TextType::class,[
                'label' => 'Prenom',
            ])
            ->add('last_name',TextType::class,[
                'label' => 'Nom',
            ])
            ->add('mariage_year', IntegerType::class, [
                'required' => false,
                'label' => 'Année de mariage',
                'attr' => [
                    'min' => 1900,
                    'max' => date('Y'),
                ],
            ])
            ->add('mariage_place',TextType::class,[
                'label' => 'Lieu de mariage',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partner::class,
        ]);
    }
}
