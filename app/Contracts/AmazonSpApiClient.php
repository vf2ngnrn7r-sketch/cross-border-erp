<?php

namespace App\Contracts;

use App\Models\AmazonAccount;

/**
 * Amazon Selling Partner API 客户端契约
 * 真实实现可替换 StubAmazonSpApiClient；当前仓库仅提供桩实现
 */
interface AmazonSpApiClient
{
    /** 校验凭证是否可用（桩模式始终返回 true） */
    public function validateCredentials(AmazonAccount $account): bool;

    /** 拉取订单列表（桩返回模拟数据） */
    public function getOrders(AmazonAccount $account, array $params = []): array;

    /** 拉取库存（桩返回模拟数据） */
    public function getInventory(AmazonAccount $account, array $params = []): array;

    /** 拉取 Listing（桩返回模拟数据） */
    public function getListings(AmazonAccount $account, array $params = []): array;

    /** 创建报表请求 */
    public function createReport(AmazonAccount $account, string $reportType, array $options = []): array;

    /** 查询报表状态 */
    public function getReport(AmazonAccount $account, string $reportId): array;

    /** 拉取财务事件组 / 结算相关数据 */
    public function getFinancialEvents(AmazonAccount $account, array $params = []): array;

    /** 拉取广告活动（Advertising API 桩） */
    public function getAdCampaigns(AmazonAccount $account, array $params = []): array;

    /** 拉取退货 */
    public function getReturns(AmazonAccount $account, array $params = []): array;
}
