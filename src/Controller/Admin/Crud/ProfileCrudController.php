<?php

namespace Base\Broadcast\Controller\Admin\Crud;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Broadcast\Entity\Profile;
use Base\Field\AssociationField;
use Base\Field\IdField;
use Base\Field\TextareaField;
use Base\Field\TextField;

/** The artists' pages: their address, their story, their picture (the artist itself is omnibase/music's Performer). */
class ProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Profile::class; }
    public static function getPreferredIcon(): ?string { return 'fa-solid fa-user-large'; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield AssociationField::new('performer', '@broadcast.admin.profile.performer')->setColumns(6)->setFormTypeOption('disabled', true);
        yield TextField::new('slug', '@broadcast.admin.profile.slug')->setColumns(6);
        yield TextField::new('image', '@broadcast.admin.profile.image')->setColumns(12)->hideOnIndex()->setRequired(false);
        yield TextareaField::new('biography', '@broadcast.admin.profile.biography')->hideOnIndex()->setRequired(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW)
            ->setPermissions(array_fill_keys([Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE], 'ROLE_ADMIN'));
    }
}
