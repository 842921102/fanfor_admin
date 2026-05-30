<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsMeFunnelWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $f = $this->miniappAnalytics()->meFunnel($this->now(), $this->analyticsDays());

        return $table
            ->heading('个人中心漏斗（'.$this->rangeLabel().'）')
            ->records(fn (): Collection => collect([
                ['step' => '页面浏览', 'count' => $f['me_page_view'], ArrayRecord::getKeyName() => 'me-pv'],
                ['step' => '点击登录', 'count' => $f['login_click'], ArrayRecord::getKeyName() => 'me-login'],
                ['step' => '登录成功', 'count' => $f['login_success'], ArrayRecord::getKeyName() => 'me-login-ok'],
                ['step' => '导航点击', 'count' => $f['nav_clicks'], ArrayRecord::getKeyName() => 'me-nav'],
                ['step' => '资料保存', 'count' => $f['profile_saves'], ArrayRecord::getKeyName() => 'me-save'],
            ]))
            ->columns([
                TextColumn::make('step')->label('步骤'),
                TextColumn::make('count')->label('次数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
