<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class UsersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Users::class; // Ensure this correctly points to your Users entity
    }

    public function configureFields(string $pageName): iterable
    {
        dump('Controller is being called'); // Debugging line to verify controller invocation

        return [
            IdField::new('id')->hideOnForm(), // Hide ID field on forms
            TextField::new('username'), // Ensure this field exists in your Users entity
            TextField::new('email'),
            TextField::new('password'), // Manage passwords carefully
            // Add other fields as needed
        ];
    }
}
