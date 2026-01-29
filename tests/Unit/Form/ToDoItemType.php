<?php

namespace App\Form;

use App\Entity\ToDoItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
// If you want to expose dueDate/createdDate in the form, you can add DateTimeType
// use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToDoItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('status', TextType::class)
            ->add('priority', IntegerType::class);

        // If you want to include dates in the unit form test, uncomment:
        // ->add('dueDate', DateTimeType::class, ['widget' => 'single_text', 'required' => false])
        // ->add('createdDate', DateTimeType::class, ['widget' => 'single_text'])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ToDoItem::class,
        ]);
    }
}