<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Wish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class)
            ->add('note', ChoiceType::class, [
                'choices' => [
                    '☆☆☆☆☆' => 0,
                    '★☆☆☆☆' => 1,
                    '★★☆☆☆' => 2,
                    '★★★☆☆' => 3,
                    '★★★★☆' => 4,
                    '★★★★★' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Note',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
