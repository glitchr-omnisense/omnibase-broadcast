<?php

namespace Base\Broadcast\Controller\Admin\Crud;

use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Admin\Filter\Filters;
use Base\Broadcast\Entity\Performance;
use Base\Field\AssociationField;
use Base\Field\BooleanField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\TextField;

/**
 * What each film is. The filter "recognised: no" lists the lives whose
 * title the import did not understand: the administrators give them their
 * song and their artists here.
 */
class PerformanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Performance::class; }
    public static function getPreferredIcon(): ?string { return 'fa-solid fa-microphone-lines'; }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('recognised')->add('kind')->add('cover')->add('episode');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('rawTitle', '@broadcast.admin.performance.raw_title')->setColumns(12)->setFormTypeOption('disabled', true);
        yield AssociationField::new('episode', '@broadcast.admin.performance.episode')->setColumns(4)->setFormTypeOption('disabled', true);
        yield TextField::new('kind', '@broadcast.admin.performance.kind')->setColumns(3);
        yield IntegerField::new('position', '@broadcast.admin.performance.position')->setColumns(2)->hideOnIndex();
        yield BooleanField::new('recognised', '@broadcast.admin.performance.recognised')->setColumns(3);
        yield AssociationField::new('song', '@broadcast.admin.performance.song')->setColumns(6)->setRequired(false);
        yield BooleanField::new('cover', '@broadcast.admin.performance.cover')->setColumns(2);
        // Read here, not edited: omnibase/admin's association widget cannot list omnibase/music's
        // performers (their `kind` is a PHP enum it turns into a string) - docs/back-office.md.
        yield TextField::new('billing', '@broadcast.admin.performance.performers')->setColumns(12)->setFormTypeOption('disabled', true)->setFormTypeOption('mapped', false)->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW)
            ->setPermissions(array_fill_keys([Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_RETURN, Action::SAVE_AND_CONTINUE], 'ROLE_ADMIN'));
    }
}
