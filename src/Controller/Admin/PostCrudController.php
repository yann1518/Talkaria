<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled(),
            TextField::new('title'),
            TextEditorField::new('content'),
            DateTimeField::new('createdAt')->setDisabled(),
            DateTimeField::new('updatedAt')->setDisabled(),
            TextField::new('category'),
            TextField::new('slug')
                ->setDisabled(), 
            AssociationField::new('comments'),
            AssociationField::new('users'),
        ];
    }
}
