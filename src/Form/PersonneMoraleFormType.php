<?php

namespace App\Form;

use App\Entity\Contrat;
use App\Entity\PersonneMorale;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonneMoraleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('RC', TextType::class, [
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('identifiant_fiscal', TextType::class, [
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('iCE', TextType::class, [
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('ville', TextType::class, [
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('adresse', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('roles',CollectionType::class, [
                'label' => 'roles',
                'entry_type' => RoleType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersonneMorale::class,
        ]);
    }
}
