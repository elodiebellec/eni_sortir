<?php

namespace App\Form;


use App\Entity\City;
use App\Entity\Location;
use App\Entity\Outing;

use App\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    protected $repoCity;
    public function __construct(CityRepository $cityRepository)
    {
        $this->repoCity=$cityRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $citiesList=$this->repoCity->findAll();
        $citiesNameList=array();

        for($i=0; $i<sizeof($citiesList); $i++)
        {
            $name = $citiesList[$i]->getName();
            $citiesNameList[] = $name;
        }

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

            //I try to add a list of city name with CollectionType but list is not diplayed
            /*
            ->add('city', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['attr' => ['class' => 'name']],
                'mapped' => false
            ])
           */
            ->add('city', ChoiceType::class, ['choices'=>$citiesNameList, 'label'=>'Ville'])

            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('saveAndAdd', SubmitType::class, ['label'=>'Publier'])
            ->add('cancel', ResetType::class, ['label'=>'Annuler'])
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
