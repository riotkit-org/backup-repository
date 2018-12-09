<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Form\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\EditForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see CreationForm
 */
class EditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('strategy',          TextType::class)
            ->add('maxBackupsCount',   IntegerType::class)
            ->add('maxCollectionSize', TextType::class)
            ->add('maxOneVersionSize', TextType::class)
            ->add('description',       TextType::class)
            ->add('collection',        EntityType::class, [
                'class' => BackupCollection::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => EditForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
