<?php

namespace App\Services\Amazon;

use App\Models\AmazonAccount;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * 从 resources/amazon/fixtures 加载 JSON，并替换时间/UUID 等占位符
 */
class FixtureLoader
{
    public function __construct(
        protected ?string $basePath = null
    ) {
        $this->basePath = $basePath ?: (string) config('amazon.fixtures.path');
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param  array<string, string>  $extra
     * @return array<string, mixed>
     */
    public function load(string $relativePath, AmazonAccount $account, array $extra = []): array
    {
        $path = $this->resolvePath($relativePath, $account);

        if (! File::exists($path)) {
            throw new RuntimeException("Amazon fixture missing: {$relativePath} (looked at {$path})");
        }

        $raw = File::get($path);
        $raw = $this->interpolate($raw, $account, $extra);

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in Amazon fixture: {$path}");
        }

        return $decoded;
    }

    /** 优先账号覆盖目录，再回退默认 fixture */
    public function resolvePath(string $relativePath, AmazonAccount $account): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        if ($account->seller_id) {
            $override = $this->basePath.'/accounts/'.$account->seller_id.'/'.$relativePath;
            if (File::exists($override)) {
                return $override;
            }
        }

        return $this->basePath.'/'.$relativePath;
    }

    /** @return list<string> */
    public function listRelativePaths(): array
    {
        if (! File::isDirectory($this->basePath)) {
            return [];
        }

        return collect(File::allFiles($this->basePath))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.json'))
            ->map(fn ($f) => ltrim(str_replace($this->basePath, '', $f->getPathname()), DIRECTORY_SEPARATOR))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $extra
     */
    protected function interpolate(string $raw, AmazonAccount $account, array $extra = []): string
    {
        $replacements = array_merge([
            '{{now}}' => now()->toIso8601String(),
            '{{account_id}}' => (string) $account->id,
            '{{seller_id}}' => (string) ($account->seller_id ?: 'UNKNOWN'),
            '{{uuid}}' => (string) Str::uuid(),
        ], $extra);

        $raw = strtr($raw, $replacements);

        // {{now_sub_hours:N}} / {{now_sub_days:N}}
        $raw = preg_replace_callback(
            '/\{\{now_sub_hours:(\d+)\}\}/',
            fn ($m) => now()->subHours((int) $m[1])->toIso8601String(),
            $raw
        ) ?? $raw;

        $raw = preg_replace_callback(
            '/\{\{now_sub_days:(\d+)\}\}/',
            fn ($m) => now()->subDays((int) $m[1])->toIso8601String(),
            $raw
        ) ?? $raw;

        // {{now_add_days:N}} / {{now_add_hours:N}}
        $raw = preg_replace_callback(
            '/\{\{now_add_days:(\d+)\}\}/',
            fn ($m) => now()->addDays((int) $m[1])->toIso8601String(),
            $raw
        ) ?? $raw;

        $raw = preg_replace_callback(
            '/\{\{now_add_hours:(\d+)\}\}/',
            fn ($m) => now()->addHours((int) $m[1])->toIso8601String(),
            $raw
        ) ?? $raw;

        return $raw;
    }
}
