<?php

namespace Bithoven\LLMManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LLMUserWorkspacePreference extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'llm_user_workspace_preferences';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the preference.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a specific config value using dot notation.
     *
     * Example: $preference->getConfigValue('features.monitor.enabled')
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set a specific config value using dot notation.
     *
     * Example: $preference->setConfigValue('features.monitor.enabled', false)
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setConfigValue(string $key, $value): self
    {
        $config = $this->config;
        data_set($config, $key, $value);
        $this->config = $config;

        return $this;
    }

    /**
     * Merge new config values with existing ones.
     *
     * @param array $newConfig
     * @return self
     */
    public function mergeConfig(array $newConfig): self
    {
        $this->config = array_replace_recursive($this->config, $newConfig);

        return $this;
    }
}
