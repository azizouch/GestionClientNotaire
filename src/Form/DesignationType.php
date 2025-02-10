<?php

namespace App\Form;

use App\Entity\Designation;
use App\Form\Type\RowType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('montant_TTC', NumberType::class, [
                'label' => 'Montant TTC ',
                'required' => true,
                'attr' => ['inputmode' => 'decimal'], // Helps with numeric keyboards
            ])
            ->add('montant_HT', NumberType::class, [
                'label' => 'Montant HT ',
                'required' => true,
                'attr' => ['inputmode' => 'decimal'], // Helps with numeric keyboards
            ])
            ->add('TVA', NumberType::class, [
                'label' => 'Montant TVA ',
                'required' => true,
                'attr' => ['inputmode' => 'decimal'], // Helps with numeric keyboards
            ])
            ->add('delai', TextType::class, [
                'label' => "Delai" ,
                'required' => true,
            ])
            ->add('etage', ChoiceType::class, [
                'choices' => [
                    'Rez-de-chaussée' => 'Rez-de-chaussée',
                    '1er étage' => '1er étage',
                    '2ème étage' => '2ème étage',
                    '3ème étage' => '3ème étage',
                    '4ème étage' => '4ème étage',
                    '5ème étage' => '5ème étage',
                    '6ème étage' => '6ème étage',
                    '7ème étage' => '7ème étage',
                    '8ème étage' => '8ème étage',
                    '9ème étage' => '9ème étage',
                    '10ème étage' => '10ème étage',
                ],
                'placeholder' => 'Sélectionnez un étage',
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
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data['montant_TTC'] = $this->floatvalue($data['montant_TTC']);
            $data['montant_HT'] = $this->floatvalue($data['montant_HT']);
            $data['TVA'] = $this->floatvalue($data['TVA']);
            $event->setData($data);
        });

    }
    // Define the floatvalue function
    private function floatvalue($val): float
    {
        // Remove non-breaking spaces (U+202F) and regular spaces
        $val = str_replace(["\u{202F}", " ", "\u{00A0}"], "", $val); // Remove non-breaking spaces and regular spaces

        // Replace comma with a dot for decimal conversion
        $val = str_replace(",", ".", $val);

        // Remove all dots (for thousands) except the last one
        $lastDotPosition = strrpos($val, '.');
        if ($lastDotPosition !== false) {
            // Remove all dots first
            $val = str_replace('.', '', $val);
            // Insert a dot back at the last dot position
            $val = substr_replace($val, '.', $lastDotPosition, 0);
        }

        // Convert to float
        return floatval($val);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Designation::class,
        ]);
    }
}