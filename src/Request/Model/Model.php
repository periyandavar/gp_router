<?php

namespace Request\Model;

interface Model
{
    public function setValues(array $values);

    public function getValues();
}