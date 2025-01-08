<?php

namespace App\Form;

use App\Form\RoleType;
use App\Entity\PersonnePhysique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PersonnePhysiqueFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'label' => 'Prenom',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('prenom_pere', TextType::class, [
                'label' => 'Prenom du père',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('prenom_mere', TextType::class, [
                'label' => 'Prenom du mère',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('cin', TextType::class, [
                'label' => 'CIN',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('telephone', NumberType::class, [
                'label' => 'Telephone',
                'required' => false,
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('nationality', TextType::class, [
                'label' => 'Nationalité',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('expiration_cin', null, [
                'label' => 'Date expiration',
                'widget' => 'single_text'
            ])
            ->add('birth_date', null, [
                'label' => 'Date de naissance',
                'widget' => 'single_text'
            ])
            ->add('birth_place', TextType::class, [
                'label' => 'Lieu de naissance',

            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse ',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Male' => 'Male',
                    'Femelle' => 'Femelle',
                ],
                'placeholder' => 'Choose a gender...',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('situation', ChoiceType::class, [
                'label' => 'Situation familiale',
                'choices' => [
                    'Célibataire' => 'Célibataire',
                    'Marié' => 'Marié',
                    'Divorcé' => 'Divorcé',
                    'Veuf' => 'Veuf',
                    'Veuve' => 'Veuve',
                ],
                'placeholder' => 'Choose a situation...',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('civilite', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Monsieur' => 'Monsieur',
                    'Madame' => 'Madame',
                    'Mademoiselle' => 'Mademoiselle',
                ],
                'placeholder' => 'Choisissez une civilité...',
                'attr' => ['class' => 'form-control shadow-sm'],
            ])
            ->add('roles',CollectionType::class, [
                'label' => 'roles',
                'entry_type' => RoleType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('partenaire', CollectionType::class, [
                'label' => 'Partenaires',
                'entry_type' => PartnerType::class,
                'entry_options' => ['label' => 'Partenaire'],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersonnePhysique::class,
        ]);
    }
}
//            ->add('role', ChoiceType::class, [
//                'label' => 'Role',
//                'choices' => [
//                    'Promettant' => 'promettant',
//                    'Bénéficiaire' => 'beneficiaire',
//                ],
//                'placeholder' => 'Choose a role...',
//                'attr' => ['class' => 'form-control'],
//            ])