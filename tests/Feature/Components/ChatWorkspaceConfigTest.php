<?php

namespace Bithoven\LLMManager\Tests\Feature\Components;

use Bithoven\LLMManager\Tests\TestCase;
use Bithoven\LLMManager\View\Components\Chat\ChatWorkspace;
use Bithoven\LLMManager\View\Components\Chat\Workspace;
use Illuminate\View\Component;

/**
 * Feature tests for ChatWorkspace components with config array
 * 
 * Tests backward compatibility, config priority, helper methods,
 * and conditional rendering.
 */
class ChatWorkspaceConfigTest extends TestCase
{
    /**
     * Test Workspace component accepts config array
     */
    public function test_workspace_component_accepts_config_array(): void
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
            'ui' => [
                'buttons' => [
                    'monitor_toggle' => false, // Must be false when monitor disabled
                ],
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertInstanceOf(Component::class, $component);
        $this->assertFalse($component->config['features']['monitor']['enabled']);
    }

    /**
     * Test ChatWorkspace component accepts config array
     */
    public function test_chat_workspace_component_accepts_config_array(): void
    {
        $config = [
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'tabs' => [
                        'console' => true,
                        'request_inspector' => false,
                        'activity_log' => false,
                    ],
                ],
            ],
        ];

        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertInstanceOf(Component::class, $component);
        $this->assertTrue($component->config['features']['monitor']['enabled']);
        $this->assertTrue($component->config['features']['monitor']['tabs']['console']);
        $this->assertFalse($component->config['features']['monitor']['tabs']['request_inspector']);
    }

    /**
     * Test Workspace backward compatibility with legacy props
     */
    public function test_workspace_backward_compatibility_with_legacy_props(): void
    {
        // When using legacy props, config is auto-built from defaults
        // showMonitor=true allows tabs to remain enabled (default)
        $component = new Workspace(
            session: null,
            configurations: collect([]),
            showMonitor: true,  // Use true to avoid validation conflict
            showToolbar: true   // Use true to avoid validation conflict
        );

        $this->assertInstanceOf(Component::class, $component);
        $this->assertTrue($component->config['features']['monitor']['enabled']);
        $this->assertTrue($component->config['features']['toolbar']);
    }

    /**
     * Test ChatWorkspace backward compatibility with legacy props
     */
    public function test_chat_workspace_backward_compatibility_with_legacy_props(): void
    {
        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            showMonitor: true
        );

        $this->assertInstanceOf(Component::class, $component);
        $this->assertTrue($component->config['features']['monitor']['enabled']);
    }

    /**
     * Test config array has priority over legacy props
     */
    public function test_config_array_has_priority_over_legacy_props(): void
    {
        $config = [
            'features' => [
                'monitor' => ['enabled' => false],
            ],
        ];

        // Pass both config and legacy props (config should win)
        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config,    // Config array (should win)
            showMonitor: true   // Legacy prop (should be ignored)
        );

        $this->assertFalse($component->config['features']['monitor']['enabled']);
    }

    /**
     * Test isMonitorTabEnabled helper method
     */
    public function test_is_monitor_tab_enabled_helper_method(): void
    {
        $config = [
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'tabs' => [
                        'console' => true,
                        'request_inspector' => false,
                        'activity_log' => false,
                    ],
                ],
            ],
        ];

        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertTrue($component->isMonitorTabEnabled('console'));
        $this->assertFalse($component->isMonitorTabEnabled('request_inspector'));
        $this->assertFalse($component->isMonitorTabEnabled('activity_log'));
        $this->assertFalse($component->isMonitorTabEnabled('nonexistent-tab'));
    }

    /**
     * Test conditional rendering based on monitor enabled
     * 
     * NOTE: Full rendering test removed - requires settings table and migrations.
     * Component render tested in integration/browser tests instead.
     */
    public function test_conditional_rendering_monitor_enabled(): void
    {
        $config = [
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'tabs' => [
                        'console' => true,
                        'request_inspector' => true,
                        'activity_log' => true,
                    ],
                ],
            ],
        ];

        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        // Verify component config
        $this->assertTrue($component->config['features']['monitor']['enabled']);
        $this->assertTrue($component->config['features']['monitor']['tabs']['console']);
    }

    /**
     * Test conditional rendering based on monitor disabled
     */
    public function test_conditional_rendering_monitor_disabled(): void
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

        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        // Verify showMonitor is false
        $this->assertFalse($component->showMonitor);
    }

    /**
     * Test conditional tab rendering
     */
    public function test_conditional_tab_rendering(): void
    {
        $config = [
            'features' => [
                'monitor' => [
                    'enabled' => true,
                    'tabs' => [
                        'console' => true,
                        'request_inspector' => false,
                        'activity_log' => false,
                    ],
                ],
            ],
        ];

        $component = new ChatWorkspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        // Console tab should be enabled
        $this->assertTrue($component->isMonitorTabEnabled('console'));
        
        // Other tabs should be disabled
        $this->assertFalse($component->isMonitorTabEnabled('request_inspector'));
        $this->assertFalse($component->isMonitorTabEnabled('activity_log'));
    }

    /**
     * Test Workspace UI layout configuration
     */
    public function test_workspace_ui_layout_configuration(): void
    {
        $config = [
            'ui' => [
                'layout' => [
                    'chat' => 'compact',
                    'monitor' => 'sidebar',
                ],
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertEquals('compact', $component->config['ui']['layout']['chat']);
        $this->assertEquals('sidebar', $component->config['ui']['layout']['monitor']);
    }

    /**
     * Test Workspace UI mode configuration
     */
    public function test_workspace_ui_mode_configuration(): void
    {
        $config = [
            'ui' => [
                'mode' => 'canvas-only',
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertEquals('canvas-only', $component->config['ui']['mode']);
    }

    /**
     * Test Workspace custom CSS class configuration
     */
    public function test_workspace_custom_css_class_configuration(): void
    {
        $config = [
            'advanced' => [
                'custom_css_class' => 'my-custom-class',
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertEquals('my-custom-class', $component->config['advanced']['custom_css_class']);
    }

    /**
     * Test Workspace performance settings
     */
    public function test_workspace_performance_settings(): void
    {
        $config = [
            'performance' => [
                'lazy_load_tabs' => false,
                'cache_preferences' => false,
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertFalse($component->config['performance']['lazy_load_tabs']);
        $this->assertFalse($component->config['performance']['cache_preferences']);
    }

    /**
     * Test Workspace complete config override
     */
    public function test_workspace_complete_config_override(): void
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
                'toolbar' => false,
                'persistence' => false,
            ],
            'ui' => [
                'mode' => 'demo',
                'layout' => [
                    'chat' => 'drawer',
                ],
                'buttons' => [
                    'new_chat' => false,
                    'clear' => false,
                    'settings' => false,
                    'download' => false,
                    'monitor_toggle' => false,
                ],
            ],
        ];

        $component = new Workspace(
            session: null,
            configurations: collect([]),
            config: $config
        );

        $this->assertFalse($component->config['features']['monitor']['enabled']);
        $this->assertFalse($component->config['features']['toolbar']);
        $this->assertFalse($component->config['features']['persistence']);
        $this->assertEquals('demo', $component->config['ui']['mode']);
        $this->assertEquals('drawer', $component->config['ui']['layout']['chat']);
    }
}
