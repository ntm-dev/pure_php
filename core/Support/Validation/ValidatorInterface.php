<?php

namespace Core\Support\Validation;

/**
 * Validator interface.
 *
 * @author Nguyen The Manh <nguyenthemanh26011996@gmail.com>
 */
interface ValidatorInterface
{
    /**
     * Define rules for request.
     */
    public function rules();

    /**
     * Define message for defined rules.
     */
    public function messages();
}
