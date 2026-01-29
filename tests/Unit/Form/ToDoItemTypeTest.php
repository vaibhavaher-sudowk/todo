<?php

namespace App\Tests\Unit\Form;

use App\Entity\ToDoItem;
use App\Form\ToDoItemType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class ToDoItemTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new PreloadedExtension([new ToDoItemType()], []),
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'title'       => 'Test ToDo',
            'description' => 'Some details',
            'status'      => 'OPEN',
            'priority'    => 5,
        ];

        $model = new ToDoItem();
        $form = $this->factory->create(ToDoItemType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Test ToDo', $model->getTitle());
        $this->assertSame('Some details', $model->getDescription());
        $this->assertSame('OPEN', $model->getStatus());
        $this->assertSame(5, $model->getPriority());

        $view = $form->createView();
        $this->assertArrayHasKey('title', $view->children);
        $this->assertArrayHasKey('description', $view->children);
        $this->assertArrayHasKey('status', $view->children);
        $this->assertArrayHasKey('priority', $view->children);
    }
}