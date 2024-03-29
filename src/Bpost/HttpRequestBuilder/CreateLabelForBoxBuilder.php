<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

class CreateLabelForBoxBuilder extends CreateLabelBuilder
{
    protected function getUrlPrefix()
    {
        return 'boxes';
    }
}
