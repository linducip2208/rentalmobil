@php
    use App\Models\SystemSetting;

    $fonts = [
        'Inter' => ['family' => 'Inter', 'url' => 'Inter:wght@400;500;600;700;800'],
        'Manrope' => ['family' => 'Manrope', 'url' => 'Manrope:wght@400;500;600;700;800'],
        'Plus Jakarta Sans' => ['family' => 'Plus Jakarta Sans', 'url' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800'],
        'DM Sans' => ['family' => 'DM Sans', 'url' => 'DM+Sans:wght@400;500;600;700'],
        'System UI' => ['family' => 'system-ui', 'url' => null],
    ];
    $font = $fonts[SystemSetting::get('admin_font', 'Inter')] ?? $fonts['Inter'];
@endphp
@if($font['url'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $font['url'] }}&display=swap" rel="stylesheet">
@endif
<style>
    :root {
        --rm-admin-font: '{{ $font['family'] }}', ui-sans-serif, system-ui, sans-serif;
        --rm-sidebar-start: {{ SystemSetting::get('admin_sidebar_color', '#0b1426') }};
        --rm-sidebar-end: {{ SystemSetting::get('admin_sidebar_end_color', '#101c31') }};
        --rm-sidebar-text: {{ SystemSetting::get('admin_sidebar_text_color', '#e2e8f0') }};
        --rm-sidebar-muted: {{ SystemSetting::get('admin_sidebar_muted_color', '#94a3b8') }};
        --rm-sidebar-icon: {{ SystemSetting::get('admin_sidebar_icon_color', '#7dd3fc') }};
        --rm-menu-active: {{ SystemSetting::get('admin_active_menu_color', '#2563eb') }};
        --rm-admin-accent: {{ SystemSetting::get('admin_accent_color', '#f59e0b') }};
        --rm-content-background: {{ SystemSetting::get('admin_content_background', '#f4f7f9') }};
        --rm-primary: {{ SystemSetting::get('primary_color', '#2563eb') }};
    }
</style>
