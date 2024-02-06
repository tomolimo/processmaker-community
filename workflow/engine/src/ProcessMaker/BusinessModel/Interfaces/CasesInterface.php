<?php

namespace ProcessMaker\BusinessModel\Interfaces;

interface CasesInterface
{
    public function setProperties(array $properties);
    public function getData();
    public function getCounter();
}
