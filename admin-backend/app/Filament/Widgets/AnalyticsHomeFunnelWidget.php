<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\UsesMiniappAnalytics;
use Filament\Support\ArrayRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class AnalyticsHomeFunnelWidget extends TableWidget
{
    use UsesMiniappAnalytics;

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $f = $this->miniappAnalytics()->homeFunnel($this->now(), $this->analyticsDays());

        return $table
            ->heading('首页漏斗（'.$this->rangeLabel().'）')
            ->records(fn (): Collection => collect([
                ['step' => '页面浏览', 'count' => $f['home_page_view'], ArrayRecord::getKeyName() => 'home-pv'],
                ['step' => '点击生成', 'count' => $f['generate_start'], ArrayRecord::getKeyName() => 'home-start'],
                ['step' => '生成成功', 'count' => $f['generate_success'], ArrayRecord::getKeyName() => 'home-success'],
                ['step' => '收藏操作', 'count' => $f['favorite_toggle'], ArrayRecord::getKeyName() => 'home-fav'],
                ['step' => '生成图片', 'count' => $f['image_generate'], ArrayRecord::getKeyName() => 'home-img'],
            ]))
            ->columns([
                TextColumn::make('step')->label('步骤'),
                TextColumn::make('count')->label('次数')->numeric()->alignEnd(),
            ])
            ->paginated(false);
    }
}
