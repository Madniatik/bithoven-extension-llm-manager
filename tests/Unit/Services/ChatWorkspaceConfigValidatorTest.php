<?php

namespace Bithoven\LLMManager\Tests\Unit\Services;

use Bithoven\LLMManager\Services\ChatWorkspaceConfigValidator;
use Bithoven\LLMManager\Tests\TestCase;
use InvalidArgumentException;

/**
 * Unit tests for ChatWorkspaceConfigValidator
 */
class ChatWorkspaceConfigValidatorTest extends TestCase
{
    /**
     * Test that empty config returns defaults
     */
    public function test_empty_config_returns_defaults(): void
    {
        $config = ChatWorkspaceConfigValidator::validate([]);
        $defaults = ChatWorkspaceConfigValidator::getDefaults();

        $this->assertEquals($defaults, $config);
    }

    /**
     * Test that valid config passes validation
     */
    public function test_valid_config_passes(): void
    {
        $config = [
            'features' => [
                'monitor' => [
                    'enabled' => false,
                    'tabs' => [
                        'console' => false,
                        'request_inspector' => false,
                        'activity_log' => false,
                    ],
                ],
            ],
        ];

        $validated = ChatWorkspaceConfigValidator::validate($config);

        $this->assertFalse($validated['features']['monitor']['enabled']);
        $this->assertFalse($validated['features']['monitor']['tabs']['console']);
    }

    /**
     * Test that partial config merges with defaults
     */
    public function test_partial_config_merges_with_defaults(): void
    {
        $config = [
            'ui' => [
                'layout' => [
                    'chat' => 'compact',
                ],
            ],
        ];

        $validated = ChatWorkspaceConfigValidator::validate($config);

        // Overridden value
        $this->assertEquals('compact', $validated['ui']['layout']['chat']);
        
        // Default values preserved
        $this->assertEquals('split-horizontal', $validated['ui']['layout']['monitor']);
        $this->assertTrue($validated['features']['monitor']['enabled']);
    }

    /**
     * Test invalid chat layout throws exception
     */
    public function test_invalid_chat_layout_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid chat workspace configuration');

        ChatWorkspaceConfigValidator::validate([
            'ui' => [
                'layout' => [
                    'chat' => 'invalid-layout',
                ],
            ],
        ]);
    }

    /**
     * Test invalid monitor layout throws exception
     */
    public function test_invalid_monitor_layout_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ChatWorkspaceConfigValidator::validate([
            'ui' => [
                'layout' => [
                    'monitor' => 'invalid-monitor-layout',
                ],
            ],
        ]);
    }

    /**
     * Test enabling tabs when monitor disabled throws exception
     */
    public function test_enabling_tabs_when_monitor_disabled_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot enable monitor tabs when monitor.enabled is false');

        ChatWorkspaceConfigValidator::validate([
            'features' => [
                'monitor' => [
                    'enabled' => false,
                    'tabs' => [
                        'console' => true, // Invalid: tab enabled but monitor disabled
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test enabling toolbar buttons when toolbar disabled throws exception
     */
    public function test_enabling_buttons_when_toolbar_disabled_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot enable toolbar buttons when toolbar feature is disabled');

        ChatWorkspaceConfigValidator::validate([
            'features' => [
                'toolbar' => false,
            ],
            'ui' => [
                'buttons' => [
                    'new_chat' => true, // Invalid: button enabled but toolbar disabled
                ],
            ],
        ]);
    }

    /**
     * Test enabling monitor_toggle when monitor disabled throws exception
     */
    public function test_enabling_monitor_toggle_when_monitor_disabled_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot enable monitor_toggle button when monitor is disabled');

        ChatWorkspaceConfigValidator::validate([
            'features' => [
                'monitor' => [
                    'enabled' => false,
                ],
            ],
            'ui' => [
                'buttons' => [
                    'monitor_toggle' => true, // Invalid
                ],
            ],
        ]);
    }

    /**
     * Test all tabs disabled when monitor enabled throws exception
     */
    public function test_all_tabs_disabled_when_monitor_enabled_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one monitor tab must be enabled when monitor is enabled');

        ChatWorkspaceConfigValidator::validate([
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'tabs' => [
                        'console' => false,
                        'request_inspector' => false,
                        'activity_log' => false,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test valid mode values
     */
    public function test_valid_mode_values(): void
    {
        $modes = ['full', 'demo', 'canvas-only'];

        foreach ($modes as $mode) {
            $validated = ChatWorkspaceConfigValidator::validate([
                'ui' => ['mode' => $mode],
            ]);

            $this->assertEquals($mode, $validated['ui']['mode']);
        }
    }

    /**
     * Test invalid mode throws exception
     */
    public function test_invalid_mode_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ChatWorkspaceConfigValidator::validate([
            'ui' => ['mode' => 'invalid-mode'],
        ]);
    }

    /**
     * Test custom CSS class accepts string
     */
    public function test_custom_css_class_accepts_string(): void
    {
        $validated = ChatWorkspaceConfigValidator::validate([
            'advanced' => [
                'custom_css_class' => 'my-custom-theme',
            ],
        ]);

        $this->assertEquals('my-custom-theme', $validated['advanced']['custom_css_class']);
    }

    /**
     * Test boolean values are preserved correctly
     */
    public function test_boolean_values_preserved(): void
    {
        $config = [
            'features' => [
                'persistence' => false,
                'toolbar' => false,
            ],
            'performance' => [
                'lazy_load_tabs' => false,
                'cache_preferences' => false,
            ],
            'ui' => [
                'buttons' => [
                    'new_chat' => false,
                    'clear' => false,
                    'settings' => false,
                    'download' => false,
                    'monitor_toggle' => false,
                ],
            ],
        ];

        $validated = ChatWorkspaceConfigValidator::validate($config);

        $this->assertFalse($validated['features']['persistence']);
        $this->assertFalse($validated['features']['toolbar']);
        $this->assertFalse($validated['performance']['lazy_load_tabs']);
        $this->assertFalse($validated['ui']['buttons']['new_chat']);
    }
}
