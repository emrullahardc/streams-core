<?php

namespace Anomaly\Streams\Platform\Ui\Table\Workflows;

use Anomaly\Streams\Platform\Workflow\Workflow;
use Anomaly\Streams\Platform\Ui\Support\Workflows\SetStream;
use Anomaly\Streams\Platform\Ui\Support\Workflows\LoadAssets;
use Anomaly\Streams\Platform\Ui\Support\Workflows\SetOptions;
use Anomaly\Streams\Platform\Ui\Support\Workflows\MakeInstance;
use Anomaly\Streams\Platform\Ui\Support\Workflows\SetRepository;
use Anomaly\Streams\Platform\Ui\Table\Workflows\Build\BuildRows;
use Anomaly\Streams\Platform\Ui\Support\Workflows\LoadBreadcrumb;
use Anomaly\Streams\Platform\Ui\Table\Workflows\Build\BuildViews;
use Anomaly\Streams\Platform\Ui\Table\Workflows\Build\BuildActions;
use Anomaly\Streams\Platform\Ui\Table\Workflows\Build\BuildEntries;
use Anomaly\Streams\Platform\Ui\Table\Workflows\Build\AuthorizeTable;
use Anomaly\Streams\Platform\Ui\Table\Component\Filter\Command\BuildFilters;

/**
 * Class BuildWorkflow
 *
 * @link    http://pyrocms.com/
 * @author  PyroCMS, Inc. <support@pyrocms.com>
 * @author  Ryan Thompson <ryan@pyrocms.com>
 */
class BuildWorkflow extends Workflow
{

    /**
     * The build steps.
     *
     * @var array
     */
    protected $steps = [

        /**
         * Make dat instance.
         */
        MakeInstance::class,

        /**
         * Integrate with others.
         */
        LoadAssets::class,
        LoadBreadcrumb::class,

        /**
         * Set important things.
         */
        SetStream::class,
        SetOptions::class,
        SetRepository::class,

        /**
         * Views can change nearly any aspect 
         * after this point so build them early.
         */
        BuildViews::class,

        /**
         * After views have had their way
         * we can authorize the table access.
         */
        AuthorizeTable::class,

        /**
         * Build the rest of the table.
         */
        BuildActions::class,
        BuildFilters::class,
        BuildEntries::class,
        BuildRows::class,
    ];
}