<?php

namespace App\Contracts;

use App\Models\AmazonAccount;

/**
 * Amazon Selling Partner API 客户端契约
 *
 * 驱动：
 * - fixture（默认）：读取 resources/amazon/fixtures JSON
 * - stub：进程内简易生成（兼容旧行为）
 * - http：预留真实 SP-API（未实现）
 */
interface AmazonSpApiClient
{
    /** 校验凭证是否可用（模拟驱动始终在账号存在时返回 true） */
    public function validateCredentials(AmazonAccount $account): bool;

    /** 拉取订单列表 */
    public function getOrders(AmazonAccount $account, array $params = []): array;

    /** 拉取指定订单的订单行 */
    public function getOrderItems(AmazonAccount $account, string $amazonOrderId): array;

    /** 拉取库存 */
    public function getInventory(AmazonAccount $account, array $params = []): array;

    /** 拉取 Listing */
    public function getListings(AmazonAccount $account, array $params = []): array;

    /** 创建报表请求 */
    public function createReport(AmazonAccount $account, string $reportType, array $options = []): array;

    /** 查询报表状态 */
    public function getReport(AmazonAccount $account, string $reportId): array;

    /** 拉取财务事件组 / 结算相关数据 */
    public function getFinancialEvents(AmazonAccount $account, array $params = []): array;

    /** 拉取广告活动（Advertising API 模拟） */
    public function getAdCampaigns(AmazonAccount $account, array $params = []): array;

    /** 拉取退货（可含 A-Z / 反馈组合模拟数据） */
    public function getReturns(AmazonAccount $account, array $params = []): array;

    /** FBA 入库货件 */
    public function getInboundShipments(AmazonAccount $account, array $params = []): array;

    /** 买家-卖家消息 */
    public function getBuyerMessages(AmazonAccount $account, array $params = []): array;

    /** 客服 / 账号 Case */
    public function getSupportCases(AmazonAccount $account, array $params = []): array;

    /** 广告效果报表行 */
    public function getAdPerformance(AmazonAccount $account, array $params = []): array;
}
