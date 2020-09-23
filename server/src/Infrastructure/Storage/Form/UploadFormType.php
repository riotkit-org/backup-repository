<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Form;

use App\Domain\Storage\Form\UploadForm;
use App\Infrastructure\Common\Transformer\BooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tags',    CollectionType::class, [
                'allow_add'    => true,
                'allow_delete' => true
            ])
            ->add('fileOverwrite', TextType::class)
            ->add('password',      TextType::class)
            ->add('public',        TextType::class)
            ->add('encoding',      TextType::class)
            ->add('attributes',    TextType::class, [
                'empty_data' => '{}'
            ]);

        $builder->get('public')->addModelTransformer(new BooleanTransformer());
        $builder->get('fileOverwrite')->addModelTransformer(new BooleanTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'data_class'           => UploadForm::class,
            'csrf_protection'      => false,
            'extra_fields_message' => 'This request does not support extra parameters such as {{ extra_fields }}'
        ]);
    }
}
