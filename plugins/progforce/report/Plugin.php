<?php namespace Progforce\Report;

use Backend;
use System\Classes\PluginBase;

/**
 * Report Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Report',
            'description' => 'Reports list',
            'author'      => 'progforce',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Progforce\Report\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'progforce.report.some_permission' => [
                'tab' => 'Report',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'report' => [
                'label'       => 'Report',
                'url'         => Backend::url('progforce/report/sounds'),
                'icon'        => 'icon-leaf',
                'permissions' => ['progforce.report.*'],
                'order'       => 500,
            ],
        ];
    }
}
