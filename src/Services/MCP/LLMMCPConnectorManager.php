<?php

namespace Bithoven\LLMManager\Services\MCP;

use Bithoven\LLMManager\Models\LLMMCPConnector;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Http;

class LLMMCPConnectorManager
{
    protected array $activeServers = [];

    /**
     * Start an MCP server
     */
    public function start(string $slug): array
    {
        $connector = LLMMCPConnector::where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Check if already running
        if ($connector->isRunning()) {
            return [
                'success' => true,
                'message' => 'Server already running',
                'status' => $connector->status,
            ];
        }

        $startScript = $this->getStartScript($connector);

        if (!file_exists($startScript)) {
            throw new \Exception("Start script not found: {$startScript}");
        }

        // Start server
        $process = Process::start($startScript);

        // Wait for server to start
        sleep(2);

        // Check if running
        if ($this->checkHealth($connector)) {
            $connector->markAsRunning();
            $this->activeServers[$slug] = $connector;

            return [
                'success' => true,
                'message' => 'Server started successfully',
                'status' => $connector->status,
            ];
        }

        throw new \Exception("Failed to start MCP server: {$slug}");
    }

    /**
     * Stop an MCP server
     */
    public function stop(string $slug): array
    {
        $connector = LLMMCPConnector::where('slug', $slug)->firstOrFail();

        if (!$connector->isRunning()) {
            return [
                'success' => true,
                'message' => 'Server not running',
            ];
        }

        // Kill process
        $port = $connector->configuration['port'] ?? null;
        if ($port) {
            Process::run("lsof -ti:{$port} | xargs kill -9 2>/dev/null || true");
        }

        $connector->status = 'stopped';
        $connector->save();

        unset($this->activeServers[$slug]);

        return [
            'success' => true,
            'message' => 'Server stopped successfully',
        ];
    }

    /**
     * Execute tool via MCP
     */
    public function executeTool(string $serverSlug, string $toolName, array $parameters): mixed
    {
        $connector = LLMMCPConnector::where('slug', $serverSlug)
            ->active()
            ->firstOrFail();

        if (!$connector->isRunning()) {
            // Auto-start if configured
            if (config('llm-manager.mcp.auto_start', true)) {
                $this->start($serverSlug);
            } else {
                throw new \Exception("MCP server '{$serverSlug}' is not running");
            }
        }

        // Send request to MCP server
        $url = $this->getServerUrl($connector) . "/tools/{$toolName}/execute";

        $response = Http::timeout(30)
            ->post($url, ['parameters' => $parameters]);

        if ($response->failed()) {
            throw new \Exception("MCP tool execution failed: {$response->body()}");
        }

        return $response->json();
    }

    /**
     * List available tools from MCP server
     */
    public function listTools(string $slug): array
    {
        $connector = LLMMCPConnector::where('slug', $slug)
            ->active()
            ->firstOrFail();

        if (!$connector->isRunning()) {
            return [];
        }

        $url = $this->getServerUrl($connector) . '/tools';

        $response = Http::get($url);

        if ($response->failed()) {
            return [];
        }

        return $response->json('tools', []);
    }

    /**
     * Get all active MCP servers
     */
    public function getActive(): array
    {
        return LLMMCPConnector::running()->get()->map(function ($connector) {
            return [
                'slug' => $connector->slug,
                'name' => $connector->name,
                'protocol' => $connector->protocol,
                'status' => $connector->status,
                'tools' => $this->listTools($connector->slug),
            ];
        })->toArray();
    }

    /**
     * Check server health
     */
    protected function checkHealth(LLMMCPConnector $connector): bool
    {
        try {
            $url = $this->getServerUrl($connector) . '/health';
            $response = Http::timeout(5)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get server URL
     */
    protected function getServerUrl(LLMMCPConnector $connector): string
    {
        $host = $connector->configuration['host'] ?? 'localhost';
        $port = $connector->configuration['port'] ?? 3000;

        return "http://{$host}:{$port}";
    }

    /**
     * Get start script path
     */
    protected function getStartScript(LLMMCPConnector $connector): string
    {
        $serverPath = $connector->configuration['server_path'] ?? null;

        if (!$serverPath) {
            // Default path
            $serverPath = base_path("vendor/bithoven/llm-manager/mcp-servers/{$connector->slug}");
        }

        return "{$serverPath}/start.sh";
    }

    /**
     * Start all auto-start servers
     */
    public function startAll(): array
    {
        $connectors = LLMMCPConnector::active()
            ->get()
            ->filter(fn($c) => $c->configuration['auto_start'] ?? false);

        $results = [];

        foreach ($connectors as $connector) {
            try {
                $results[$connector->slug] = $this->start($connector->slug);
            } catch (\Exception $e) {
                $results[$connector->slug] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Stop all running servers
     */
    public function stopAll(): array
    {
        $connectors = LLMMCPConnector::running()->get();

        $results = [];

        foreach ($connectors as $connector) {
            try {
                $results[$connector->slug] = $this->stop($connector->slug);
            } catch (\Exception $e) {
                $results[$connector->slug] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
