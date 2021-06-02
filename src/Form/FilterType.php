<?php

namespace App\Form;


use App\Entity\Site;
use App\Model\OutingsFilter;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    protected  $repoSite; // attribut de classe
    public function __construct( SiteRepository  $siteRepository)
    {
        $this->repoSite= $siteRepository; // initialisation de l'attribut de classe avec la valeur du repository de l'entity Site
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $listSites= $this->repoSite->findAll();// Recherche des sites en BDD
        $listWithName=array();


        for ($i=0; $i< sizeof($listSites); $i++)
        {
            //Boucle dans la liste de sites pour recuperer uniquement les noms
            $name=  $listSites[$i]->getName();
            $listWithName[$name] = $name; // creation d'une autre table qui ne contiendra que les noms des sites
                                          // name est en clé et valeur ici parce que dans twig ce qui est affiché c'est uniquement la clé
                                        // voir surement une autre solution

        }


        $builder
            ->add('site',ChoiceType::class,  [ 'choices'=>$listWithName, "label"=>"Site:"]) // ligne concernée par la liste à afficher
            ->add('name',SearchType::class, [ "required"=>false,"label"=>"Le nom de la sortie contient"])
            ->add('dateBegin', DateTimeType::class, ["required"=>false, "label"=>"Entre","html5"=>true,"empty_data" =>null, "input"=>"datetime"])
            ->add('dateEnd', DateTimeType::class, ["required"=>false, "label"=>"et","html5"=>true,"empty_data" =>null, "input"=>"datetime"])

            ->add('isPlanner', ChoiceType::class, [ "required"=>false,
                "choices" =>
                    ["Sorties dont je suis l'organisateur/trice "=>"ok"],
                    "multiple"=>true, "expanded"=>true,
                'attr' => [ 'class' => 'checkbox'], "label"=> false] )

            ->add('isRegistered', ChoiceType::class, [ "required"=>false,
                "choices" =>
                    ["Sorties auxquelles je suis inscrit/e"=>"ok"],
                     "multiple"=>true, "expanded"=>true,
                    'attr' => [ 'class' => 'checkbox'], "label"=>  false] )

            ->add('isNotRegistered', ChoiceType::class, [ "required"=>false,
                "choices" =>
                    ["Sorties auxquelles je ne suis pas inscrit/e"=>"ok"],
                     "multiple"=>true, "expanded"=>true,
                    'attr' => [ 'class' => 'checkbox'], "label"=> false] )

            ->add('isOutDated', ChoiceType::class, [ "required"=>false,
                "choices" =>
                    ["Sorties passées"=>"ok"],
                    "multiple"=>true, "expanded"=>true,
                    'attr' => [ 'class' => 'checkbox'], "label"=> false] )

            ->add('Rechercher', SubmitType::class, ["attr"=>["class"=>"send"]])

        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'=> OutingsFilter::class
        ]);
    }
}
