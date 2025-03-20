<?php

/**
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author      emerchantpay
 * @copyright   Copyright (C) 2015-2025 emerchantpay Ltd.
 * @license     http://opensource.org/licenses/MIT The MIT License
 */

namespace Genesis\Api\Traits\Request\Financial\Alternatives\Invoice;

use Genesis\Exceptions\ErrorParameter;

/**
 * Trait Items
 *
 * @package Genesis\Api\Traits\Request\Financial\Alternatives\Invoice
 */
trait InvoiceValidation
{
    /**
     * Common items validation
     *
     * @return void
     *
     * @throws ErrorParameter
     */
    public function validateItems()
    {
        // verify there is at least one item added
        if (empty($this->items) || $this->items->count() === 0) {
            throw new ErrorParameter('Empty (null) required parameter: items');
        }

        $this->items->validate();
    }

    /**
     * Validate the amount
     *
     * @return void
     *
     * @throws ErrorParameter
     */
    public function validateAmount()
    {
        if ((float)$this->amount !== (float)$this->items->getAmount()) {
            throw new ErrorParameter(
                'Amount must be equal to the sum of the items amount'
            );
        }
    }
}
