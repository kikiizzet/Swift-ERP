<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('⚡ Swift ERP')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): HtmlString => new HtmlString($this->customStyles())
            )
            ->navigationGroups([
                NavigationGroup::make('Penjualan'),
                NavigationGroup::make('Pembelian'),
                NavigationGroup::make('Inventaris'),
                NavigationGroup::make('Akuntansi'),
                NavigationGroup::make('Karyawan'),
                NavigationGroup::make('Master Data'),
                NavigationGroup::make('Pengaturan'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function customStyles(): string
    {
        return <<<'CSS'
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            /* Base font */
            * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important; }

            /* === TOP BAR === */
            /* Wrapper nav */
            .fi-topbar,
            nav.fi-topbar {
                position: sticky !important;
                top: 0 !important;
                z-index: 20 !important;
                background: #0f172a !important;
                border-bottom: 1px solid transparent !important;
                background-clip: padding-box !important;
                box-shadow:
                    0 1px 0 0 rgba(99,102,241,0.4),
                    0 4px 24px rgba(0,0,0,0.4) !important;
            }

            /* Inner content row */
            .fi-topbar > div,
            .fi-topbar nav > div {
                height: 3.75rem !important;
                padding: 0 1.25rem !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.75rem !important;
                background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%) !important;
            }

            /* Brand / Logo area */
            .fi-logo,
            .fi-brand-logo,
            [class*="fi-logo"],
            [class*="brand"] {
                font-size: 1.05rem !important;
                font-weight: 700 !important;
                letter-spacing: -0.02em !important;
                background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%) !important;
                -webkit-background-clip: text !important;
                -webkit-text-fill-color: transparent !important;
                background-clip: text !important;
            }

            /* Topbar icon buttons (hamburger, notifications, etc.) */
            .fi-topbar button,
            .fi-topbar [role="button"] {
                border-radius: 0.5rem !important;
                transition: background 0.15s, transform 0.15s !important;
                color: #94a3b8 !important;
            }
            .fi-topbar button:hover,
            .fi-topbar [role="button"]:hover {
                background: rgba(255,255,255,0.08) !important;
                color: #e2e8f0 !important;
            }

            /* User avatar circle */
            .fi-topbar .fi-avatar,
            .fi-topbar [class*="avatar"] {
                background: linear-gradient(135deg, #3b82f6, #8b5cf6) !important;
                color: white !important;
                font-weight: 700 !important;
                font-size: 0.875rem !important;
                border: 2px solid rgba(255,255,255,0.15) !important;
                border-radius: 9999px !important;
                width: 2.25rem !important;
                height: 2.25rem !important;
            }

            /* Breadcrumbs */
            .fi-breadcrumbs ol { gap: 0.25rem !important; }
            .fi-breadcrumbs li { color: #64748b !important; font-size: 0.8125rem !important; }
            .fi-breadcrumbs li:last-child { color: #94a3b8 !important; font-weight: 500 !important; }
            .fi-breadcrumbs a { color: #64748b !important; }
            .fi-breadcrumbs a:hover { color: #94a3b8 !important; }


            /* === SIDEBAR === */
            .fi-sidebar { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important; border-right: 1px solid rgba(255,255,255,0.06) !important; }
            .fi-sidebar-nav-groups { padding: 0.75rem !important; }
            .fi-sidebar-group > .fi-sidebar-group-label { color: rgba(148,163,184,0.6) !important; font-size: 0.65rem !important; font-weight: 600 !important; letter-spacing: 0.1em !important; text-transform: uppercase !important; }
            .fi-sidebar-item-button { border-radius: 0.625rem !important; transition: all 0.2s !important; color: rgba(203,213,225,0.85) !important; }
            .fi-sidebar-item-button:hover { background: rgba(255,255,255,0.07) !important; color: #f1f5f9 !important; transform: translateX(2px) !important; }
            .fi-sidebar-item-button.fi-active { background: linear-gradient(135deg, rgba(59,130,246,0.25), rgba(99,102,241,0.2)) !important; color: #93c5fd !important; font-weight: 600 !important; box-shadow: inset 0 0 0 1px rgba(99,102,241,0.25) !important; }

            /* === CONTENT === */
            .fi-main { background: #0f172a !important; }
            .fi-simple-main { background: #0f172a !important; min-height: 100vh !important; }

            /* === LOGIN PAGE === */
            .fi-simple-layout { background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%) !important; }
            .fi-simple-page { background: rgba(30, 41, 59, 0.9) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 1.25rem !important; box-shadow: 0 25px 50px rgba(0,0,0,0.5) !important; backdrop-filter: blur(16px) !important; padding: 2rem !important; }

            /* === CARDS / SECTIONS === */
            .fi-section { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.07) !important; border-radius: 1rem !important; box-shadow: 0 4px 16px rgba(0,0,0,0.2) !important; }
            .fi-card { background: #1e293b !important; border-radius: 1rem !important; }
            .fi-section-header-heading { color: #e2e8f0 !important; font-weight: 600 !important; }

            /* === TABLE === */
            .fi-ta-ctn { border-radius: 1rem !important; overflow: hidden !important; background: #1e293b !important; border: 1px solid rgba(255,255,255,0.07) !important; box-shadow: 0 4px 16px rgba(0,0,0,0.2) !important; }
            .fi-ta table thead tr th { background: #263347 !important; color: rgba(148,163,184,0.9) !important; font-size: 0.7rem !important; font-weight: 600 !important; letter-spacing: 0.06em !important; text-transform: uppercase !important; border-bottom: 1px solid rgba(255,255,255,0.07) !important; }
            .fi-ta table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.04) !important; transition: background 0.15s !important; }
            .fi-ta table tbody tr:hover { background: rgba(59,130,246,0.05) !important; }
            .fi-ta table tbody td { color: #cbd5e1 !important; font-size: 0.875rem !important; }

            /* === SKELETON SHIMMER === */
            @keyframes shimmer {
                0%   { background-position: -200% center; }
                100% { background-position: 200% center; }
            }
            /* Base shimmer layer — shared by all skeleton elements */
            .fi-ta-skeleton,
            .fi-ta-skeleton td,
            [wire\:loading] .fi-ta tbody tr td,
            .fi-skeleton,
            [class*="skeleton"] {
                background: linear-gradient(
                    90deg,
                    #1e293b 0%,
                    #2d3f55 30%,
                    #3b5068 50%,
                    #2d3f55 70%,
                    #1e293b 100%
                ) !important;
                background-size: 300% 100% !important;
                animation: shimmer 1.8s ease-in-out infinite !important;
                border-radius: 0.375rem !important;
                color: transparent !important;
                border-color: transparent !important;
                user-select: none !important;
            }
            /* Hide inner content when skeleton */
            .fi-ta-skeleton td > *,
            .fi-ta-skeleton td span {
                visibility: hidden !important;
            }
            /* Skeleton row spacing */
            .fi-ta-skeleton td {
                padding: 1rem !important;
                height: 3.25rem !important;
            }
            /* Widget skeleton */
            .fi-wi-stats-overview-stat[aria-busy="true"],
            .fi-wi-chart[aria-busy="true"] {
                background: linear-gradient(90deg, #1e293b 0%, #2d3f55 50%, #1e293b 100%) !important;
                background-size: 300% 100% !important;
                animation: shimmer 1.8s ease-in-out infinite !important;
            }


            /* === STAT WIDGETS === */
            .fi-wi-stats-overview-stat { background: linear-gradient(135deg, #1e293b, #263347) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 1rem !important; box-shadow: 0 4px 16px rgba(0,0,0,0.2) !important; transition: transform 0.2s, box-shadow 0.2s !important; position: relative !important; overflow: hidden !important; }
            .fi-wi-stats-overview-stat::before { content: '' !important; position: absolute !important; top: 0 !important; left: 0 !important; right: 0 !important; height: 3px !important; background: linear-gradient(90deg, #3b82f6, #8b5cf6) !important; }
            .fi-wi-stats-overview-stat:hover { transform: translateY(-2px) !important; box-shadow: 0 12px 32px rgba(0,0,0,0.3) !important; }
            .fi-wi-stats-overview-stat-value { font-size: 1.5rem !important; font-weight: 700 !important; color: #f1f5f9 !important; }
            .fi-wi-stats-overview-stat-label { color: rgba(148,163,184,0.85) !important; font-size: 0.8rem !important; }

            /* === CHART WIDGET === */
            .fi-wi-chart { background: linear-gradient(135deg, #1e293b, #263347) !important; border: 1px solid rgba(255,255,255,0.08) !important; border-radius: 1rem !important; }

            /* === BUTTONS === */
            .fi-btn-primary { background: linear-gradient(135deg, #3b82f6, #6366f1) !important; border: none !important; box-shadow: 0 4px 12px rgba(99,102,241,0.3) !important; border-radius: 0.625rem !important; font-weight: 600 !important; transition: all 0.2s !important; }
            .fi-btn-primary:hover { background: linear-gradient(135deg, #2563eb, #4f46e5) !important; box-shadow: 0 6px 20px rgba(99,102,241,0.4) !important; transform: translateY(-1px) !important; }

            /* === FORMS === */
            .fi-input { background: #263347 !important; border-color: rgba(255,255,255,0.1) !important; color: #e2e8f0 !important; border-radius: 0.625rem !important; }
            .fi-input:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important; }
            .fi-label label, .fi-fo-field-wrp label { color: #94a3b8 !important; font-size: 0.8125rem !important; font-weight: 500 !important; }

            /* === BADGES === */
            .fi-badge { border-radius: 9999px !important; font-weight: 600 !important; letter-spacing: 0.025em !important; }

            /* === MODALS === */
            .fi-modal-content { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 1.25rem !important; box-shadow: 0 25px 60px rgba(0,0,0,0.5) !important; }

            /* === DROPDOWN === */
            .fi-dropdown-panel { background: #1e293b !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 0.875rem !important; box-shadow: 0 20px 40px rgba(0,0,0,0.4) !important; }
            .fi-dropdown-list-item-label { color: #cbd5e1 !important; }

            /* === SCROLLBAR === */
            ::-webkit-scrollbar { width: 6px; height: 6px; }
            ::-webkit-scrollbar-track { background: #0f172a; }
            ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
            ::-webkit-scrollbar-thumb:hover { background: #475569; }
        </style>
CSS;
    }
}
