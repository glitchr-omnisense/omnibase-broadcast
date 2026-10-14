<?php

namespace Base\Broadcast\Controller\Admin\Crud;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Broadcast\Entity\Episode;
use Base\Broadcast\Entity\Programme;
use Base\Field\AssociationField;
use Base\Field\DateField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\TextField;

/** The episodes: number, date, picture. They come from the import; the administrators correct them. */
class EpisodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Episode::class; }
    public static function getPreferredIcon(): ?string { return 'fa-solid fa-tv'; }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield IntegerField::new('number', '@broadcast.admin.episode.number')->setColumns(2);
        yield DateField::new('airedAt', '@broadcast.admin.episode.aired_at')->setRequired(false)->setColumns(3);
        yield TextField::new('title', '@broadcast.admin.episode.title')->setColumns(7)->setRequired(false);
        yield AssociationField::new('programme', '@broadcast.admin.episode.programme')->setColumns(6)->hideOnIndex();
        yield TextField::new('image', '@broadcast.admin.episode.image')->setColumns(12)->hideOnIndex()->setRequired(false);
        yield TextField::new('origin', '@broadcast.admin.episode.origin')->setColumns(12)->hideOnIndex()->setRequired(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->setPermissions(array_fill_keys([Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_ADD_ANOTHER], 'ROLE_ADMIN'));
    }

    public function createEntity(string $entityFqcn): object
    {
        $programme = $this->entityManager->getRepository(Programme::class)->findOneBy([], ['id' => 'ASC']) ?? new Programme('Programme');
        $last = $this->entityManager->getRepository(Episode::class)->findOneBy([], ['number' => 'DESC']);

        return new Episode($programme, ($last?->getNumber() ?? 0) + 1);
    }
}
