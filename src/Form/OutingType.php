<?php

namespace App\Form;


use App\Entity\City;
use App\Entity\Location;
use App\Entity\Outing;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', textType::class, [
                'label'=> 'Nom de la sortie'
            ])
            ->add('dateBegin', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label'=> 'Date et heure de la sortie'
            ])
            ->add('dateEnd', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label'=> 'Date limite inscription'
            ])
            ->add('maxRegistration', NumberType::class, [
                'label'=> 'Nombre de places'
            ])
            ->add('duration', NumberType::class, [
                'html5' => true,
                'label'=> 'Durée'
            ])

            ->add('description',TextareaType::class, [
                'required' => false,
                'label'=> 'Description et infos'
            ])
            ->add('location', EntityType::class, [
                'class' => location::class,
                'choice_label' => 'name'
            ])

            /*->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'city.name',
                'mapped' => false
            ])*/

            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('saveAndAdd', SubmitType::class, ['label'=>'Publier'])
            ->getForm();

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
