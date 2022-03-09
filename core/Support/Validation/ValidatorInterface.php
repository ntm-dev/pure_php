<?php

namespace Support\Validation;

/**
 * Validator interface.
 *
 * @author Nguyen The Manh <manh.nguyen3@ntq-solution.com.vn>
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
