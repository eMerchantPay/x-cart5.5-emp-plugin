<?php

namespace spec\Genesis\Api\Constants\Transaction\Parameters\Business;

use Genesis\Api\Constants\Transaction\Parameters\Business\PaymentTypes;
use PhpSpec\ObjectBehavior;

class PaymentTypesSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PaymentTypes::class);
    }

    public function it_should_be_array()
    {
        $this::getAll()->shouldBeArray();
    }
}
