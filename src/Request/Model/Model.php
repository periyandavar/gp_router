<?php

namespace Router\Request\Model;

interface Model
{
    /**
     * Set the values for the model
     *
     * @param array $values Values to set in the model
     *
     * @return void
     */
    public function setValues(array $values);

    /**
     * Get the values of the model
     *
     * @return array Values of the model
     */
    public function getValues(): array;
}
