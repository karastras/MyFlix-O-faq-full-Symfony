<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Character;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

class ShowType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On utlise le builder de formulaire pour ajouter
        // les champs que l'on souhaite voir apparaitre dasn notre formulaire
        // Le premier arguemnt de la méthode 'add'
        // sera le nom d'une propriété de l'entité
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la série',
                'attr' => [
                    'placeholder' => 'Saisir le nom de la série'
                ]
            ])
            ->add('trailer', TextType::class, [
                'attr' => [
                    'placeholder' => 'Renseigner l\'URL '
                ]
            ])
            ->add('synopsis', TextAreaType::class, [
                'attr' => [
                    'placeholder' => 'Résumé de la série'
                ]
            ])
            ->add('categories', EntityType::class, [
                'label'  => 'catégorie(s)',
                'class'=> Category::class, // je cible l'entité Category
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,  // case à cocher 
            ])
            ->add('characters', EntityType::class, [
                'label'  => 'catégorie(s)',
                'class'=> Character::class, // je cible l'entité Character
                'choice_label' => 'fullname',
                'multiple' => true,
                'expanded' => true,  // case à cocher 
            ])
            ->add('releasedAt', DateType::class, [
                'label' => 'Date de création de la série',
                'widget' => 'single_text'
            ])
            ->add('minimum_age', ChoiceType::class, [
                'label' => 'Age minimum requis:',
                'expanded' => true,
                'choices'  => [
                    'Pas de limite' => '0',
                    '10' => '10',
                    '11'=> '11',
                    '12'=> '12',
                    '16'=> '16',
                    '18' => '18',

                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider'
            ]);
    } 

}