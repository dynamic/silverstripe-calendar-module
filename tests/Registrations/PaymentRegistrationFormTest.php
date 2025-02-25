<?php declare(strict_types = 1);

namespace TitleDK\Calendar\Tests\Registrations;

use SilverStripe\Dev\SapphireTest;
use TitleDK\Calendar\Registrations\EventRegistrationController;
use TitleDK\Calendar\Registrations\PaymentRegistrationForm;

class PaymentRegistrationFormTest extends SapphireTest
{
    public function testConstruct(): void
    {
        $form = PaymentRegistrationForm::create(EventRegistrationController::create(), 'paymentFormTest');
        $fields = $form->Fields()->toArray();
        $names = \array_map(static function ($field) {
            return $field->Name;
        }, $fields);
        $this->assertEquals([
            'Name',
            'PayersName',
            'Email',
            'NumberOfTickets',
            'Notes',
            'EventID',
            'SecurityID',
        ], $names);
    }


    public function testSetDone(): void
    {
        $form = PaymentRegistrationForm::create(EventRegistrationController::create(), 'paymentFormTest');
        $form->setDone();
        $fields = $form->Fields()->toArray();
        $names = \array_map(static function ($field) {
            return $field->Name;
        }, $fields);
        $this->assertEquals([
            'CompleteMsg',
            'SecurityID',
        ], $names);
    }


    public function testDoRegister(): void
    {
        $this->markTestSkipped('TODO');
    }


    public function testSetFormField(): void
    {
        $form = PaymentRegistrationForm::create(EventRegistrationController::create(), 'eventFormTest');
        $form->setFormField('Email', 'fred@fred.com');

        /** @var \TitleDK\Calendar\Tests\Registrations\FieldList $fields */
        $fields = $form->Fields();
        $emailField = $fields->fieldByName('Email');
        $this->assertEquals('fred@fred.com', $emailField->Value());
    }
}
