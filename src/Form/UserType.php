<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, [
                'label' => 'Prénom'
            ])
            ->add('lastname', null, [
                'label' => 'Nom'
            ])
            ->add('email', EmailType::class);
            
            /**
             * On se branche à l'évènement PRE_SET_DATA pour customiser bien en amont
             * le rendu du formulaire en fonction de l'utilisateur (existant ou non)
             */
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                $form = $event->getForm();
                $submitedData = $event->getData();
                
                // On recherche le Role de l'user connecté
                // Une fois l'évènement intercepté, on va pouvoir faire des vérifications sur l'utilisateur
                // On met en place une logique métier spécifique à notre projet
                
                if ($submitedData->getId()) {
                    $currentUser = $this->security->getUser()->getRoles();
                    // L'utilisateur existe...On est dans un formulaire de mise à jour
                    //...
                    $form->add('password', PasswordType::class, [
                        'label' => 'Mot de passe',
                        'trim' => true // Suppression d'espace avant et après le mot de passe
                        // 'always_empty'=>false,
                    ]);

                    // Condition mise pour afficher ou non en fonction du role de l'utilisateur                   
                    if (in_array("ROLE_SUPER_ADMIN", $currentUser) ) {
                        
                        $form->add('roles', ChoiceType::class, [
                            'label' => 'Rôle utilisateur',
                            'required' => true,
                            'multiple' => true,
                            'expanded' => true,
                            'choices'  => [
                                'User' => 'ROLE_USER',
                                'Admin' => 'ROLE_ADMIN',
                            ],
                        ]);
                    }
                } else {
                    // L'utilisateur n'existe pas...On est dans un formulaire de création
                    // On ajoute donc le champ password avec le RepeatedType
                    $form->add('password', RepeatedType::class, [
                        'type' => PasswordType::class,
                        'invalid_message' => 'Les mot de passe doivent être identiques',
                        'required' => true,
                        'first_options'  => ['label' => 'Mot de passe'],
                        'second_options' => ['label' => 'Repeter le mot de passe'],
                    ]);
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}