<?php
namespace tests\AppBundle\Form\Type;

use AppBundle\Form\Type\FeedbackType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Unit Tests for forms
 */
class FeedbackTypeTest extends TypeTestCase
{

    public function testSubmitValidData()
    {
        $data = array(
            'name' => 'test',
            'e-mail' => 'test2',
            'comment' => 'test3',
        );

        $form = $this->factory->create(FeedbackType::class);

        // submit the data to the form directly
        $form->submit($data);
        $formData = $form->getData();

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

    }


}
