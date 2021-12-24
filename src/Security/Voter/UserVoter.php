<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;

// tuto suivi https://www.youtube.com/watch?v=wSh9zlL2xzc
class UserVoter extends Voter
{
    // Les constantes sont utilisé pour un soucis de maintenabilité du code
    // Si le subject change dans le controller, il n'y a qu'à le remplacer dans la 
    // constante et le reste du code sera de suite opérationnel
    const USER_SHOW = 'user_show';
    const USER_UPDATE = 'user_update';
    const USER_DELETE = 'user_delete';
    
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    // 1ère étape: vérifier si les demandes de permission existent dans ce Voter
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::USER_SHOW ,self::USER_UPDATE, self::USER_DELETE])
            && $subject instanceof \App\Entity\User;
    }


    // 2ème étape: vérifie si on respecte les critères/conditions pour donner les permissions ou non
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // Vérifie si le user est connecté
        if (!$user instanceof UserInterface) {
            return false;
        }
        // dd($user, $subject);

        // Ajouter des rôles ayant droits avec tous les attributs, sinon le mettre dans le swich ou la fonction privée ciblés
        // if($this->security->isGranted("ROLE_SUPER_ADMIN")) {
        //     return true;
        // }

        // On peut ajouter une étape pour vérifier si la class ciblé à un propriétaire (ex: pour un post ou annonce)
        // Ce qui n'est pas le cas ici puisque qu'on agit dans le cadre de User (difficle d'avoir un propriétaire au User)
        // if (null === $subject->getUsers()) return false);

        switch ($attribute) {
            case self::USER_SHOW:
                return $this->canShow($user, $subject);
                break;
            case self::USER_UPDATE:
                return $this->canUpdate($user, $subject);
                break;
            case self::USER_DELETE:
                return $this->canDelete($user, $subject);
                break;
        }

        return false;
    }


    private function canShow(User $user, $subject){
        return $subject === $user | $this->security->isGranted('ROLE_ADMIN');
    }

    private function canUpdate(User $user, $subject){
        return $subject === $user | $this->security->isGranted('ROLE_SUPER_ADMIN');
    }

    private function canDelete(User $user, $subject){
        return $subject === $user | $this->security->isGranted('ROLE_SUPER_ADMIN');
    }

    // private function canDelete(User $user, User $subject){
    //     if($this->security->isGranted("ROLE_SUPER_ADMIN")) {
    //         return true;
    //     } 
    //     return $user === $subject;
    //  dd($user, $subject);
    // } 

    // On pourrait ajouter une étape en créant des fonctions privées qui seront appelées dans le switch
    // Elles sont placés en dehors du reste pour un soucis de visibilité 
    //ex:

    // private function canEdit(Annonce $subject, User $user){
    //  return $user === $subject->getUser();
    // } 
    
    // Dans le switch :
    // case self::ANNONCE_EDIT:
    //      return $this->canEdit($subject, $user);
    // break;
}
