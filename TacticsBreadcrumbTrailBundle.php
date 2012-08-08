<?php

namespace Tactics\Bundle\BreadcrumbTrailBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TacticsBreadcrumbTrailBundle extends Bundle
{
    public function getParent()
    {
        return 'APYBreadcrumbTrailBundle';
    }
}
