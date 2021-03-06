<?php

namespace App\Form;


use App\Entity\Location;
use App\Entity\Outing;

use App\Repository\CityRepository;
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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $citiesList=$this->repoCity->findBY([],['name'=>'ASC']);
        $citiesNameList=array();

        for($i=0; $i<sizeof($citiesList); $i++)
        {
            $name = $citiesList[$i]->getName();
            $citiesNameList[$name] = $name;
        }



        $builder
            ->add('name', textType::class, [
                'label'=> false,
                'attr' => array(
                    'placeholder' => 'Nom de la sortie'
                )
            ])
            ->add('dateBegin', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label'=> false
            ])
            ->add('dateEnd', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label'=> false
            ])
            ->add('maxRegistration', NumberType::class, [
                'label'=> false,
                'attr' => array(
                    'placeholder' => 'Nombre de places'
                )
            ])
            ->add('duration', NumberType::class, [
                'html5' => true,
                'label'=> false,
                'attr' => array(
                    'placeholder' => 'Dur??e en mn'
                )
            ])

            ->add('description',TextareaType::class, [
                'required' => false,
                'label'=> false,
                'attr' => array(
                    'placeholder' => 'Description et infos'
                )
            ])
            ->add('location', EntityType::class, [
                'class' => location::class,
                'choice_label' => 'name',
                'placeholder' => 'S??lectionner une localisation'
            ])

            ->add('city', ChoiceType::class, ['choices'=>$citiesNameList, 'label'=>false, 'mapped' => false, 'placeholder' => 'S??lectionner une ville'])


            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('saveAndAdd', SubmitType::class, ['label'=>'Publier', 'attr' => ['class' => 'btn-secondary']])
            ->getForm();


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}
