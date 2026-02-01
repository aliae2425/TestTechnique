<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Dom\Text;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Form\Button;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addTab('Utilisateur');
        yield EmailField::new('email', 'Email');
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices();
        yield BooleanField::new('isVerified', 'Vérifié');

        yield FormField::addTab('Détails personnels');
        yield TextField::new('username', 'Nom d\'utilisateur');
        yield TextField::new('name', 'Prénom');
        yield TextField::new('lastName', 'Nom de famille');
        yield IntegerField::new('lvl', 'Niveau');
        yield IntegerField::new('xp', 'XP');
        
        yield FormField::addTab('Resultats Quiz');
        yield FormField::addTab('formations');

        }
    
}
