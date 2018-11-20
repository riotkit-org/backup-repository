<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\UploadByPostForm;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadByPostFormType extends UploadFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('fileName', TextType::class, [
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => UploadByPostForm::class,
            'csrf_protection'      => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}',
            'allow_extra_fields'   => true // _token
        ]);
    }
}
