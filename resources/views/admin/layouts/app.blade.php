<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '跨境 ERP') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --erp-bg: #0f172a;
            --erp-panel: #1e293b;
            --erp-accent: #38bdf8;
            --erp-muted: #94a3b8;
        }
        body.erp-admin { background: linear-gradient(160deg, #0f172a 0%, #1e293b 45%, #0c4a6e 100%); min-height: 100vh; color: #e2e8f0; }
        .erp-shell { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
        .erp-nav { background: rgba(15, 23, 42, .92); border-right: 1px solid rgba(148,163,184,.2); padding: 1.25rem 1rem; }
        .erp-nav a { display: block; padding: .55rem .75rem; border-radius: .5rem; color: #cbd5e1; text-decoration: none; margin-bottom: .2rem; font-size: .92rem; }
        .erp-nav a:hover, .erp-nav a.active { background: rgba(56,189,248,.15); color: #7dd3fc; }
        .erp-brand { font-weight: 700; font-size: 1.15rem; margin-bottom: 1.5rem; color: #f8fafc; letter-spacing: .02em; }
        .erp-main { padding: 1.5rem 2rem; }
        .erp-card { background: rgba(30,41,59,.85); border: 1px solid rgba(148,163,184,.18); border-radius: .75rem; padding: 1rem 1.25rem; }
        .erp-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .erp-table th, .erp-table td { padding: .65rem .5rem; border-bottom: 1px solid rgba(148,163,184,.15); text-align: left; }
        .erp-table th { color: #94a3b8; font-weight: 600; }
        .erp-badge { display: inline-block; padding: .15rem .5rem; border-radius: .35rem; font-size: .75rem; background: rgba(56,189,248,.2); color: #7dd3fc; }
        .erp-btn { display: inline-block; padding: .45rem .9rem; border-radius: .45rem; background: #0284c7; color: #fff; text-decoration: none; font-size: .875rem; border: none; cursor: pointer; }
        .erp-btn:hover { background: #0369a1; }
        .erp-btn-ghost { background: transparent; border: 1px solid rgba(148,163,184,.35); color: #e2e8f0; }
        .erp-flash { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.35); color: #86efac; padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1rem; }
        .erp-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
        .erp-stat { text-align: center; }
        .erp-stat .n { font-size: 1.6rem; font-weight: 700; color: #7dd3fc; }
        .erp-stat .l { font-size: .8rem; color: #94a3b8; }
        .erp-input { width: 100%; padding: .5rem .65rem; border-radius: .4rem; border: 1px solid rgba(148,163,184,.3); background: rgba(15,23,42,.6); color: #e2e8f0; }
        .erp-label { display: block; margin-bottom: .35rem; color: #94a3b8; font-size: .85rem; }
        .erp-form-row { margin-bottom: 1rem; }
        @media (max-width: 900px) {
            .erp-shell { grid-template-columns: 1fr; }
            .erp-nav { border-right: none; border-bottom: 1px solid rgba(148,163,184,.2); }
        }
    </style>
</head>
<body class="erp-admin">
<div class="erp-shell">
    <aside class="erp-nav">
        <div class="erp-brand">跨境 ERP</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">总览</a>
        <a href="{{ route('admin.amazon-accounts.index') }}" class="{{ request()->routeIs('admin.amazon-accounts.*') ? 'active' : '' }}">亚马逊账号</a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">商品目录</a>
        <a href="{{ route('admin.listings.index') }}" class="{{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">Listing</a>
        <a href="{{ route('admin.inventories.index') }}" class="{{ request()->routeIs('admin.inventories.*') ? 'active' : '' }}">库存 FBA/FBM</a>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">订单</a>
        <a href="{{ route('admin.finance.index') }}" class="{{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">财务结算</a>
        <a href="{{ route('admin.advertising.index') }}" class="{{ request()->routeIs('admin.advertising.*') ? 'active' : '' }}">广告</a>
        <a href="{{ route('admin.returns.index') }}" class="{{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">退货 / A-Z / 反馈</a>
        <a href="{{ route('admin.sync-jobs.index') }}" class="{{ request()->routeIs('admin.sync-jobs.*') ? 'active' : '' }}">同步任务</a>
        <hr style="border-color:rgba(148,163,184,.2);margin:1rem 0">
        <a href="{{ route('dashboard') }}">Breeze 面板</a>
        <a href="{{ route('profile.edit') }}">个人资料</a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top:.5rem">
            @csrf
            <button type="submit" class="erp-btn erp-btn-ghost" style="width:100%">退出登录</button>
        </form>
    </aside>
    <main class="erp-main">
        @if (session('success'))
            <div class="erp-flash">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
