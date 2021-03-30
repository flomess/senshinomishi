<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('pseudo'),
            DateField::new('date_inscription'),
            EmailField::new('mail', 'Adresse Mail'),
            TextField::new('password')->hideOnIndex(),
            IntegerField::new('nb_parties', 'Parties'),
            IntegerField::new('nb_victoires', 'Victoires'),
            TextField::new('presentation', 'Présentation'),
            BooleanField::new('is_verified', 'Vérification Mail'),
        ];
    }

}
