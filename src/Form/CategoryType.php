<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de la catégorie',
                'help' => 'Saisissez une catégorie non existante',
                'attr' => ['placeholder'=>'Saisir un nom de catégorie']
            ])
            // P.S : Il est également possible d'ajouter ce bouton de soumission
            // depuis le template, entre les fonctions "form_start" et "form_end"
            // Voir Bonnes pratiques symfony : https://symfony.com/doc/current/best_practices.html#add-form-buttons-in-templates
            // ->add('save', SubmitType::class, [
            //     'label' => 'Valider'
            // ]); // J'ajoute un bouton de soumission du formulaire
        ;
    }

    // Utile à Symfony dans le cas ou utilise plusieurs formulaires dans un
    // même template : il permet à Symfony de construire plus facilement la page de formulaire
    // setDefaults : permet de lier ce FormType à son entity
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
