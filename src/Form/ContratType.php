<?php

namespace App\Form;

use App\Entity\Contrat;
use App\Entity\Designation;
use App\Form\DesignationType;
use App\Entity\PersonneMorale;
use App\Entity\PersonnePhysique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('repertoir',null, [
                'attr' => [
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('type',null,[
                'required' => false,
            ])
            ->add('date_promettant', null, [
                'widget' => 'single_text'
            ])
            ->add('date_beneficiaire', null, [
                'widget' => 'single_text'
            ])
            ->add('date_maitre', null, [
                'widget' => 'single_text'
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ])
            ->add('designation', DesignationType::class, [
                'label' => 'Designation',
                'required' => true,
            ])
            ->add('pphysique', CollectionType::class, [
                'label' => 'Personnes Physiques',
                'entry_type' => PersonnePhysiqueFormType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('pmorale', CollectionType::class, [
                'label' => 'Personnes Morales',
                'entry_type' => PersonneMoraleFormType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('Submit',SubmitType::class,[
                'label' => 'Create Compromis',
                'attr' => [
                    'class' => 'btn btn-primary mt-5',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
        ]);
    }
}
