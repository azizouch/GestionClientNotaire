<?php

namespace App\Form;

use App\Entity\Procuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_foncier',null, [
                'attr' => [
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('date_mandant', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('date_mandataire', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('date_maitre', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'shadow-sm',
                ],
            ])
            ->add('persons', CollectionType::class, [
                'label' => 'Persons',
                'entry_type' => PersonnePhysiqueFormType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('selectedPersons', HiddenType::class, [
                'mapped' => false, // Not mapped to the Contrat entity
                'required' => false,
            ])
            ->add('Submit',SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Procuration::class,
        ]);
    }
}
