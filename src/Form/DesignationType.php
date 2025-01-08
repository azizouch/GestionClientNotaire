<?php

namespace App\Form;

use App\Entity\Designation;
use App\Form\Type\RowType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DesignationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_bien', TextType::class, [
                'label' => 'Numero de bien ',
                'required' => true,
            ])
            ->add('titre_foncier', TextType::class, [
                'label' => 'Titre foncier ',
                'required' => true,
            ])
            ->add('titre_foncier_mere', TextType::class, [
                'label' => 'Titre foncier mere ',
                'required' => true,
            ])
            ->add('montant_TTC', TextType::class, [
                'label' => 'Montant TTC ',
                'required' => true,
            ])
            ->add('TVA', TextType::class, [
                'label' => 'Montant TVA ',
                'required' => true,
            ])
            ->add('delai', TextType::class, [
                'label' => "Delai" ,
                'required' => true,
            ])
            ->add('etage', TextType::class, [
                'label' => 'Etage ',
                'required' => true,
            ])
            ->add('superficie', TextType::class, [
                'label' => 'Superficie ',
                'required' => true,
            ])
            ->add('Nombre_salon', TextType::class, [
                'label' => "Nombre de salon" ,
                'required' => true,
            ])
            ->add('nombre_chambre', TextType::class, [
                'label' => "Nombre de chambre" ,
                'required' => true,
            ])
            ->add('nombre_cuisine', TextType::class, [
                'label' => "Nombre de cuisine" ,
                'required' => true,
            ])
            ->add('nombre_sallon_de_bain', TextType::class, [
                'label' => "Nombre de salle de bain" ,
                'required' => true,
            ])
            ->add('numero_divise', TextType::class, [
                'label' => 'Numero du fraction divise',
                'required' => true,
            ])
            ->add('indivision', TextType::class, [
                'label' => "L'indivision" ,
                'required' => true,
            ])
            ->add('residence', TextType::class, [
                'label' => "La résidence" ,
                'required' => true,
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse ',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'class' => 'shadow',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Designation::class,
        ]);
    }
}