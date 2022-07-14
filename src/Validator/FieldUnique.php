<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use App\Validator\FieldUniqueValidator;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class FieldUnique extends Constraint
{
    public string $message = 'Field is not unique';

}
