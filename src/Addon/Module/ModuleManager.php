<?php namespace Anomaly\Streams\Platform\Addon\Module;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Anomaly\Streams\Platform\Addon\Module\Command\EnableModule;
use Anomaly\Streams\Platform\Addon\Module\Command\DisableModule;
use Anomaly\Streams\Platform\Addon\Module\Command\InstallModule;
use Anomaly\Streams\Platform\Addon\Module\Command\MigrateModule;
use Anomaly\Streams\Platform\Addon\Module\Command\UninstallModule;

/**
 * Class ModuleManager
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class ModuleManager
{
    use DispatchesJobs;

    /**
     * Install a module.
     *
     * @param Module $module
     * @param bool   $seed
     */
    public function install(Module $module, $seed = false)
    {
        dispatch_sync(new InstallModule($module, $seed));
    }

    /**
     * Uninstall a module.
     *
     * @param Module $module
     */
    public function uninstall(Module $module)
    {
        dispatch_sync(new UninstallModule($module));
    }

    /**
     * Enable a module.
     *
     * @param Module $module
     * @param bool   $seed
     */
    public function enable(Module $module)
    {
        dispatch_sync(new EnableModule($module));
    }

    /**
     * Disable a module.
     *
     * @param Module $module
     */
    public function disable(Module $module)
    {
        dispatch_sync(new DisableModule($module));
    }

    /**
     * Migrate a module.
     *
     * @param Module $module
     * @param bool   $seed
     */
    public function migrate(Module $module, $seed = false)
    {
        dispatch_sync(new MigrateModule($module, $seed));
    }

}
