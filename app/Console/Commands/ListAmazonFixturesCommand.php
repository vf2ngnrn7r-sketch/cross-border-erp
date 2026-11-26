<?php

namespace App\Console\Commands;

use App\Services\Amazon\FixtureLoader;
use Illuminate\Console\Command;

/** artisan amazon:fixtures:list — 列出当前 Fixture 文件 */
class ListAmazonFixturesCommand extends Command
{
    protected $signature = 'amazon:fixtures:list';

    protected $description = '列出 Amazon JSON fixture 路径与当前驱动';

    public function handle(FixtureLoader $loader): int
    {
        $this->info('AMAZON_DRIVER='.config('amazon.driver'));
        $this->info('Fixtures path: '.$loader->basePath());
        $this->newLine();

        $paths = $loader->listRelativePaths();
        if ($paths === []) {
            $this->warn('未找到任何 .json fixture');

            return self::SUCCESS;
        }

        foreach ($paths as $path) {
            $this->line('  - '.$path);
        }

        $this->newLine();
        $this->comment('账号覆盖目录示例: resources/amazon/fixtures/accounts/{seller_id}/orders/getOrders.json');

        return self::SUCCESS;
    }
}
