<?php

namespace Base\Broadcast\Controller\Admin\Crud;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Broadcast\Entity\Song;
use Base\Field\IdField;
use Base\Field\TextField;

class SongCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Song::class; }
    public static function getPreferredIcon(): ?string { return 'fa-solid fa-music'; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title', '@broadcast.admin.song.title')->setColumns(6);
        yield TextField::new('originalArtist', '@broadcast.admin.song.original')->setColumns(6)->setRequired(false);
        yield TextField::new('slug', '@broadcast.admin.song.slug')->setColumns(6)->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->setPermissions(array_fill_keys([Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_ADD_ANOTHER], 'ROLE_ADMIN'));
    }
}
