<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Form\Collection;

use App\Domain\Backup\Entity\Authentication\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Form\TokenFormAttachForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see TokenFormAttachForm
 */
class TokenAttachFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('collection',   EntityType::class, [
                'class'           => BackupCollection::class,
                'invalid_message' => 'collection_no_longer_exists'
            ])
            ->add('token',        EntityType::class, [
                'class'           => Token::class,
                'invalid_message' => 'token_not_found'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => TokenFormAttachForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
