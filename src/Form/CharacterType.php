<?php

namespace App\Form;

use App\Entity\Character;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class CharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullname', null, [
                'label' => 'Nom complet'
            ])
            ->add('picture', FileType::class, [
                'label' => 'Photo du personnage',

                // On dit ici que la propriété "picture" n'est pas une propriété de l'entité Character
                'mapped' => false,
                
                // Le champs picture ne sera pas obligatoire (pas besoin de le remplir)
                'required' => false,

                'constraints' => [
                    // new File([
                    new Image([ // Validation encore plus spécifique pour cibler des images
                        'maxSize' => '1024k' // 1Mo maximum, sinon erreur
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
        ]);
    }
}
