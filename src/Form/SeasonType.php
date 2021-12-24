<?php

namespace App\Form;

use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('number', null, [
                'label' => 'Numéro de la saison'
        ]);
        $builder->add('releasedAt', DateType::class, [
                'label'=> 'Date de diffusion',
                'widget' => 'single_text'
        ]);
        $builder->add('isOnProduction', null, [
                'label' => 'En cours de production'
        ]);
        // On rajoute un "sous-formulaire" pour ajouter des épisodes à la volée
        $builder->add('episodes', CollectionType::class, [
            'entry_type' => EpisodeType::class,
            'entry_options' => ['label'=> false],
            'allow_add' => true,
            'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Season::class,
        ]);
    }
}
