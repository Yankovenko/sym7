<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class PostEdit extends PostCreate
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $post = $options['data'];
        if (!$post instanceof Post) {
            return;
        }

        $builder
            ->add('createdAt', DateTimeType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                    'disabled' => true,
                    'value' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
                'mapped' => false,
            ])
            ->add('id', NumberType::class, [
                'required' => false,
                'attr' => [
                    'readonly' => true,
                    'disabled' => true,
                    'value' => $post->getId(),
                ],
                'mapped' => false,
            ]);
    }
}
