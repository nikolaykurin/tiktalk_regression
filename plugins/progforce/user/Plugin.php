<?php namespace Progforce\User;

use App;
use Auth;
use Backend;
use Progforce\User\Components\SLPAccount;
use Progforce\User\Components\SLPSession;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Illuminate\Foundation\AliasLoader;
use Progforce\Notify\Classes\Notifier;
use Progforce\User\Classes\UserRedirector;

class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */
    public $elevated = true;

    public function pluginDetails()
    {
        return [
            'name'        => 'progforce.user::lang.plugin.name',
            'description' => 'progforce.user::lang.plugin.description',
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'icon'        => 'icon-user',
            'homepage'    => 'https://github.com/progforce/user-plugin'
        ];
    }

    public function register()
    {
        App::singleton('redirect', function ($app) {
            // overrides with our own extended version of Redirector to support 
            // seperate url.intended session variable for frontend
            $redirector = new UserRedirector($app['url']);
             // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }
             return $redirector;
        });
    }

    public function boot() {
        $this->app->singleton(\Illuminate\Auth\AuthManager::class, function ($app) {
            return $app->make('auth');
        });

        App::singleton('auth', function ($app) {
            return new \Illuminate\Auth\AuthManager($app);
        });

        $alias = AliasLoader::getInstance();
        $alias->alias('Auth', 'Progforce\User\Facades\Auth');

        App::singleton('user.auth', function() {
            return \Progforce\User\Classes\AuthManager::instance();
        });

        $this->app['router']->middleware('jwt.auth', '\Tymon\JWTAuth\Middleware\GetUserFromToken');
        $this->app['router']->middleware('jwt.refresh', '\Tymon\JWTAuth\Middleware\RefreshToken');
    }

    public function registerComponents()
    {
        return [
            \Progforce\User\Components\Session::class       => 'session',
            \Progforce\User\Components\Account::class       => 'account',
            \Progforce\User\Components\ResetPassword::class => 'resetPassword',
            SLPSession::class => 'slp_session',
            SLPAccount::class => 'slp_account'
        ];
    }

    public function registerPermissions()
    {
        return [
            'progforce.users.access_users' => [
                'tab'   => 'progforce.user::lang.plugin.tab',
                'label' => 'progforce.user::lang.plugin.access_users'
            ],
            'progforce.users.access_groups' => [
                'tab'   => 'progforce.user::lang.plugin.tab',
                'label' => 'progforce.user::lang.plugin.access_groups'
            ],
            'progforce.users.access_settings' => [
                'tab'   => 'progforce.user::lang.plugin.tab',
                'label' => 'progforce.user::lang.plugin.access_settings'
            ],
            'progforce.users.impersonate_user' => [
                'tab'   => 'progforce.user::lang.plugin.tab',
                'label' => 'progforce.user::lang.plugin.impersonate_user'
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'user' => [
                'label'       => 'progforce.user::lang.users.menu_label',
                'url'         => Backend::url('progforce/user/users'),
                'icon'        => 'icon-user',
                'iconSvg'     => 'plugins/progforce/user/assets/images/user-icon.svg',
                'permissions' => ['progforce.users.*'],
                'order'       => 500,
            ],
            'slps' => [
                'label'       => 'SLPs',
                'url'         => Backend::url('progforce/user/slps'),
                'icon'        => 'icon-user',
                'iconSvg'     => 'plugins/progforce/user/assets/images/user-icon.svg',
                'permissions' => ['progforce.users.*'],
                'order'       => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'progforce.user::lang.settings.menu_label',
                'description' => 'progforce.user::lang.settings.menu_description',
                'category'    => SettingsManager::CATEGORY_USERS,
                'icon'        => 'icon-cog',
                'class'       => 'Progforce\User\Models\Settings',
                'order'       => 500,
                'permissions' => ['progforce.users.access_settings']
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'progforce.user::mail.activate',
            'progforce.user::mail.welcome',
            'progforce.user::mail.restore',
            'progforce.user::mail.new_user',
            'progforce.user::mail.reactivate',
            'progforce.user::mail.invite',
        ];
    }

    public function registerNotificationRules()
    {
        return [
            'groups' => [
                'user' => [
                    'label' => 'User',
                    'icon' => 'icon-user'
                ],
            ],
            'events' => [
                \Progforce\User\NotifyRules\UserActivatedEvent::class,
                \Progforce\User\NotifyRules\UserRegisteredEvent::class,
            ],
            'actions' => [],
            'conditions' => [
                \Progforce\User\NotifyRules\UserAttributeCondition::class
            ],
        ];
    }

    protected function bindNotificationEvents()
    {
        if (!class_exists(Notifier::class)) {
            return;
        }

        Notifier::bindEvents([
            'progforce.user.activate' => \Progforce\User\NotifyRules\UserActivatedEvent::class
        ]);

        Notifier::instance()->registerCallback(function($manager) {
            $manager->registerGlobalParams([
                'user' => Auth::getUser()
            ]);
        });
    }
}
