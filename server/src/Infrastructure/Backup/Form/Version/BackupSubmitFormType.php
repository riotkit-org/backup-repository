<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Form\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Form\BackupSubmitForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see BackupSubmitForm
 */
class BackupSubmitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('collection', EntityType::class, [
                'class'           => BackupCollection::class,
                'invalid_message' => 'collection_no_longer_exists'
            ])
            ->add('attributes', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => BackupSubmitForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
