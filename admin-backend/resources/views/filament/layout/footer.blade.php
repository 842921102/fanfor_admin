@php
    $version = (string) config('fanfor_release.version', 'dev');
    $env = app()->environment();
    $isProduction = $env === 'production';
@endphp

<footer class="fanfor-admin-footer">
    <div class="fanfor-admin-footer__row">
        <span class="fanfor-admin-footer__version">CMS {{ $version }}</span>
        <span @class([
            'fanfor-admin-footer__env',
            'fanfor-admin-footer__env--dev' => ! $isProduction,
        ])>
            {{ $isProduction ? '正式环境' : '开发环境' }}
        </span>
    </div>
</footer>
