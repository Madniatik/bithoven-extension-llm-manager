<?php

namespace Bithoven\LLMManager\Services;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Chat Workspace Configuration Validator
 * 
 * Valida y normaliza la configuración del componente Workspace.
 * Proporciona defaults, validación de tipos y reglas lógicas.
 * 
 * @package Bithoven\LLMManager\Services
 * @version 1.0.7
 */
class ChatWorkspaceConfigValidator
{
    /**
     * Default configuration values
     * 
     * @var array
     */
    private static array $defaults = [
        'features' => [
            'monitor' => [
                'enabled' => true,
                'default_open' => false,
                'tabs' => [
                    'console' => true,
                    'request_inspector' => true,
                    'activity_log' => true,
                ],
            ],
            'settings_panel' => true,
            'persistence' => true,
            'toolbar' => true,
        ],
        'ui' => [
            'layout' => [
                'chat' => 'bubble',
                'monitor' => 'split-horizontal',
            ],
            'buttons' => [
                'new_chat' => true,
                'clear' => true,
                'settings' => true,
                'download' => true,
                'monitor_toggle' => true,
            ],
            'mode' => 'full',
        ],
        'performance' => [
            'lazy_load_tabs' => true,
            'minify_assets' => false,
            'cache_preferences' => true,
        ],
        'advanced' => [
            'multi_instance' => false,
            'custom_css_class' => '',
            'debug_mode' => false,
        ],
    ];

    /**
     * Validation rules (Laravel validator format)
     * 
     * @var array
     */
    private static array $rules = [
        // Features
        'features.monitor.enabled' => 'boolean',
        'features.monitor.default_open' => 'boolean',
        'features.monitor.tabs.console' => 'boolean',
        'features.monitor.tabs.request_inspector' => 'boolean',
        'features.monitor.tabs.activity_log' => 'boolean',
        'features.settings_panel' => 'boolean',
        'features.persistence' => 'boolean',
        'features.toolbar' => 'boolean',
        
        // UI
        'ui.layout.chat' => 'in:bubble,drawer,compact',
        'ui.layout.monitor' => 'in:drawer,tabs,split-horizontal,split-vertical,sidebar',
        'ui.buttons.new_chat' => 'boolean',
        'ui.buttons.clear' => 'boolean',
        'ui.buttons.settings' => 'boolean',
        'ui.buttons.download' => 'boolean',
        'ui.buttons.monitor_toggle' => 'boolean',
        'ui.mode' => 'in:full,demo,canvas-only',
        
        // Performance
        'performance.lazy_load_tabs' => 'boolean',
        'performance.minify_assets' => 'boolean',
        'performance.cache_preferences' => 'boolean',
        
        // Advanced
        'advanced.multi_instance' => 'boolean',
        'advanced.custom_css_class' => 'string|nullable',
        'advanced.debug_mode' => 'boolean',
    ];

    /**
     * Validate and merge configuration with defaults
     * 
     * @param array $config User-provided configuration
     * @return array Validated and merged configuration
     * @throws InvalidArgumentException If validation fails
     */
    public static function validate(array $config): array
    {
        // 1. Merge with defaults recursively
        $merged = array_replace_recursive(self::$defaults, $config);

        // 2. Validate using dot-notation on multidimensional array (Laravel supports this)
        $validator = Validator::make($merged, self::$rules);

        if ($validator->fails()) {
            throw new InvalidArgumentException(
                'Invalid chat workspace configuration: ' . 
                $validator->errors()->first()
            );
        }

        // 3. Logical validations (complex rules)
        self::validateLogic($merged);

        return $merged;
    }

    /**
     * Get default configuration
     * 
     * @return array
     */
    public static function getDefaults(): array
    {
        return self::$defaults;
    }

    /**
     * Validate logical rules (complex inter-field validations)
     * 
     * @param array $config
     * @throws InvalidArgumentException
     */
    private static function validateLogic(array $config): void
    {
        // If monitor disabled, all tabs should be disabled
        if (!$config['features']['monitor']['enabled']) {
            $anyTabEnabled = $config['features']['monitor']['tabs']['console'] ||
                           $config['features']['monitor']['tabs']['request_inspector'] ||
                           $config['features']['monitor']['tabs']['activity_log'];
            
            if ($anyTabEnabled) {
                throw new InvalidArgumentException(
                    'Cannot enable monitor tabs when monitor.enabled is false'
                );
            }
        }

        // If toolbar disabled, button toggles should be disabled
        if (!$config['features']['toolbar']) {
            $anyButtonEnabled = array_reduce(
                $config['ui']['buttons'],
                fn($carry, $value) => $carry || $value,
                false
            );
            
            if ($anyButtonEnabled) {
                throw new InvalidArgumentException(
                    'Cannot enable toolbar buttons when toolbar feature is disabled'
                );
            }
        }

        // If monitor disabled, monitor_toggle button should be disabled
        if (!$config['features']['monitor']['enabled'] && $config['ui']['buttons']['monitor_toggle']) {
            throw new InvalidArgumentException(
                'Cannot enable monitor_toggle button when monitor is disabled'
            );
        }

        // At least one monitor tab must be enabled if monitor is enabled
        if ($config['features']['monitor']['enabled']) {
            $allTabsDisabled = !$config['features']['monitor']['tabs']['console'] &&
                              !$config['features']['monitor']['tabs']['request_inspector'] &&
                              !$config['features']['monitor']['tabs']['activity_log'];
            
            if ($allTabsDisabled) {
                throw new InvalidArgumentException(
                    'At least one monitor tab must be enabled when monitor is enabled'
                );
            }
        }
    }
}
