@extends('admin.layouts.app')

@section('title', '总览')

@section('content')
    <h1 style="font-size:1.5rem;margin-bottom:.35rem">经营总览</h1>
    <p style="color:#94a3b8;margin-bottom:1.25rem">
        当前组织：{{ $org?->name ?? '未绑定' }}
        @if($org)<span class="erp-badge">{{ $org->slug }}</span>@endif
        · <a style="color:#7dd3fc" href="{{ route('admin.reports.index') }}">查看汇总报表</a>
    </p>

    <div class="erp-stats">
        <div class="erp-card erp-stat"><div class="n">{{ $stats['products'] }}</div><div class="l">商品</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['orders'] }}</div><div class="l">订单</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['inventory_skus'] }}</div><div class="l">库存 SKU</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['purchase_orders'] }}</div><div class="l">采购单</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['shipments'] }}</div><div class="l">货件</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['campaigns'] }}</div><div class="l">广告活动</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ number_format($stats['ad_spend'], 0) }}</div><div class="l">广告花费</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ number_format($stats['ad_sales'], 0) }}</div><div class="l">广告销售</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['unread_messages'] }}</div><div class="l">未读消息</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['open_cases'] }}</div><div class="l">未结 Case</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['returns'] }}</div><div class="l">退货</div></div>
        <div class="erp-card erp-stat"><div class="n">{{ $stats['sync_pending'] }}</div><div class="l">进行中同步</div></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div class="erp-card">
            <h2 style="font-size:1.05rem;margin-bottom:.75rem">最近订单</h2>
            <table class="erp-table">
                <thead><tr><th>订单号</th><th>状态</th><th>金额</th><th>日期</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" style="color:#7dd3fc">{{ $order->amazon_order_id }}</a></td>
                        <td><span class="erp-badge">{{ $order->order_status }}</span></td>
                        <td>{{ $order->currency }} {{ $order->order_total }}</td>
                        <td>{{ optional($order->purchase_date)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:#94a3b8">暂无订单</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="erp-card">
            <h2 style="font-size:1.05rem;margin-bottom:.75rem">最近同步任务</h2>
            <table class="erp-table">
                <thead><tr><th>类型</th><th>状态</th><th>时间</th></tr></thead>
                <tbody>
                @forelse($recentJobs as $job)
                    <tr>
                        <td><a href="{{ route('admin.sync-jobs.show', $job) }}" style="color:#7dd3fc">{{ $job->job_type }}</a></td>
                        <td><span class="erp-badge">{{ $job->status }}</span></td>
                        <td>{{ $job->created_at?->format('m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="color:#94a3b8">暂无同步记录</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="erp-card">
        <h2 style="font-size:1.05rem;margin-bottom:.75rem">最近货件</h2>
        <table class="erp-table">
            <thead><tr><th>货件</th><th>类型</th><th>状态</th><th>运单</th></tr></thead>
            <tbody>
            @forelse($recentShipments as $s)
                <tr>
                    <td><a style="color:#7dd3fc" href="{{ route('admin.warehouse.show', $s) }}">{{ $s->external_shipment_id ?? '#'.$s->id }}</a></td>
                    <td>{{ $s->shipment_type }}</td>
                    <td><span class="erp-badge">{{ $s->status }}</span></td>
                    <td>{{ $s->tracking_number }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="color:#94a3b8">暂无货件</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
