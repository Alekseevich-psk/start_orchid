<?php

namespace App\Orchid\Fields;

use Orchid\Screen\Field;

class DynamicField extends Field
{
    /**
     * The Blade view used to render the field.
     *
     * @var string
     */
    protected $view = 'orchid.fields.dynamic-field';

    /**
     * Default attributes for the field.
     *
     * @var array
     */
    protected $attributes = [
        'placeholder' => 'Enter text...',
        'class'       => 'form-control',
        'type'        => 'text',
    ];

    /**
     * List of attributes available for the HTML tag.
     *
     * @var array
     */
    protected $inlineAttributes = [
        'placeholder',
        'value',
        'type',
        'name'
    ];
}
