<?php

namespace Base\Broadcast\Controller\Admin\Crud;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Broadcast\Entity\Programme;
use Base\Field\AssociationField;
use Base\Field\IdField;
use Base\Field\TextareaField;
use Base\Field\TextField;

class ProgrammeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Programme::class; }
    public static function getPreferredIcon(): ?string { return 'fa-solid fa-tower-broadcast'; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('name', '@broadcast.admin.programme.name')->setColumns(6);
        yield TextField::new('slug', '@broadcast.admin.programme.slug')->setColumns(6);
        yield AssociationField::new('channel', '@broadcast.admin.programme.channel')->setColumns(6)->setRequired(false)->hideOnIndex();
        yield TextareaField::new('description', '@broadcast.admin.programme.description')->hideOnIndex()->setRequired(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->setPermissions(array_fill_keys([Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_ADD_ANOTHER], 'ROLE_ADMIN'));
    }
}
