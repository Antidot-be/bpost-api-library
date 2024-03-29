<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

class CreateLabelForOrderBuilder extends CreateLabelBuilder
{
    protected function getUrlPrefix()
    {
        return 'orders';
    }
}
