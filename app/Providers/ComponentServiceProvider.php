<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class ComponentServiceProvider extends ServiceProvider
{
    /**
     * All component aliases.
     *
     * @var array<string, string>
     */
    protected $components = [
        // Form Components
        'form' => 'components.form',
        'form-input' => 'components.form.input',
        'form-select' => 'components.form.select',
        'form-textarea' => 'components.form.textarea',
        'form-checkbox' => 'components.form.checkbox',
        'form-button' => 'components.form.button',

        // Layout Components
        'card' => 'components.card',
        'stats-card' => 'components.stats-card',
        'page-header' => 'components.page-header',

        // Navigation Components
        'tabs' => 'components.tabs',
        'tab-trigger' => 'components.tab-trigger',
        'tab-panel' => 'components.tab-panel',
        'breadcrumb' => 'components.breadcrumb',
        'dropdown' => 'components.dropdown',

        // Feedback Components
        'alert' => 'components.alert',
        'toast' => 'components.toast',
        'spinner' => 'components.spinner',
        'empty-state' => 'components.empty-state',

        // Data Display Components
        'table' => 'components.table',
        'badge' => 'components.badge',
        'activity-log' => 'components.activity-log',
        'pagination' => 'components.pagination',

        // Modal Components
        'modal' => 'components.modal',
        'confirm-modal' => 'components.confirm-modal',

        // Filter Components
        'filter' => 'components.filter',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register components
        foreach ($this->components as $alias => $component) {
            Blade::component($component, $alias);
        }

        // Register custom if directive for role checking
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        // Register custom if directive for permission checking
        Blade::if('can', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });

        // Register custom directive for formatting dates
        Blade::directive('date', function ($expression) {
            return "<?php echo ($expression)->format('M d, Y'); ?>";
        });

        // Register custom directive for formatting currency
        Blade::directive('money', function ($amount) {
            return "<?php echo '₵' . number_format($amount, 2); ?>";
        });

        // Register custom directive for truncating text
        Blade::directive('truncate', function ($expression) {
            list($string, $length) = explode(',', $expression);
            return "<?php echo Str::limit($string, $length); ?>";
        });

        // Register custom directive for active navigation items
        Blade::directive('active', function ($route) {
            return "<?php echo request()->routeIs($route) ? 'active' : ''; ?>";
        });

        // Register custom directive for required form fields
        Blade::directive('required', function () {
            return '<span class="text-danger">*</span>';
        });
    }
}
