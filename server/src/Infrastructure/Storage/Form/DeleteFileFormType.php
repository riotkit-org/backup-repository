<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\DeleteFileForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see DeleteFileForm
 */
class DeleteFileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => DeleteFileForm::class,
            'csrf_protection'      => false,
            'allow_extra_fields'   => true, // _token
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
