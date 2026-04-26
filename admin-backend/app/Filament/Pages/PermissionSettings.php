<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\AppRole;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;
use UnitEnum;

class PermissionSettings extends Page
{
    protected static ?string $title = '权限设置';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = '权限设置';

    protected static string|UnitEnum|null $navigationGroup = '系统管理';

    protected static ?int $navigationSort = 111;

    protected string $view = 'filament.pages.permission-settings';

    /**
     * @var array<int, array{model: string, policy: string, abilities: array<int, string>}>
     */
    public array $policyRows = [];

    /**
     * @var array<int, array{value: string, label: string}>
     */
    public array $roleRows = [];

    /**
     * @var array<int, string>
     */
    public array $abilityHeaders = [];

    /**
     * @var array<int, array{resource: string, policy: string, ability: string, role_results: array<string, bool>}>
     */
    public array $matrixRows = [];

    public function mount(): void
    {
        $this->policyRows = $this->buildPolicyRows();
        $this->roleRows = array_map(
            static fn (string $role): array => ['value' => $role, 'label' => AppRole::labelCn($role)],
            AppRole::VALUES,
        );
        $this->abilityHeaders = $this->buildAbilityHeaders($this->policyRows);
        $this->matrixRows = $this->buildMatrixRows($this->policyRows);
    }

    /**
     * @return array<int, array{model: string, policy: string, abilities: array<int, string>}>
     */
    private function buildPolicyRows(): array
    {
        /** @var array<class-string, class-string> $policies */
        $policies = Gate::policies();
        ksort($policies);
        $rows = [];

        foreach ($policies as $model => $policy) {
            $rows[] = [
                'model' => $model,
                'policy' => $policy,
                'abilities' => $this->extractAbilities($policy),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function extractAbilities(string $policyClass): array
    {
        if (! class_exists($policyClass)) {
            return [];
        }

        $ref = new ReflectionClass($policyClass);
        $methods = collect($ref->getMethods())
            ->filter(fn ($m): bool => $m->isPublic() && $m->class === $policyClass)
            ->map(fn ($m): string => $m->name)
            ->reject(fn (string $name): bool => in_array($name, ['__construct', 'before'], true))
            ->values()
            ->all();

        return array_values($methods);
    }

    /**
     * @param  array<int, array{model: string, policy: string, abilities: array<int, string>}>  $policyRows
     * @return array<int, string>
     */
    private function buildAbilityHeaders(array $policyRows): array
    {
        $all = [];
        foreach ($policyRows as $row) {
            foreach ($row['abilities'] as $ability) {
                $all[] = $ability;
            }
        }

        $all = array_values(array_unique($all));
        $preferredOrder = ['viewAny', 'view', 'create', 'update', 'delete', 'deleteAny', 'toggleActive', 'changeRole'];
        usort($all, function (string $a, string $b) use ($preferredOrder): int {
            $ai = array_search($a, $preferredOrder, true);
            $bi = array_search($b, $preferredOrder, true);
            $ai = $ai === false ? 999 : $ai;
            $bi = $bi === false ? 999 : $bi;

            return $ai === $bi ? strcmp($a, $b) : ($ai <=> $bi);
        });

        return $all;
    }

    /**
     * @param  array<int, array{model: string, policy: string, abilities: array<int, string>}>  $policyRows
     * @return array<int, array{resource: string, policy: string, ability: string, role_results: array<string, bool>}>
     */
    private function buildMatrixRows(array $policyRows): array
    {
        $rows = [];

        foreach ($policyRows as $row) {
            foreach ($row['abilities'] as $ability) {
                $roleResults = [];
                foreach (AppRole::VALUES as $role) {
                    $roleResults[$role] = $this->evaluateAbility($row['policy'], $row['model'], $ability, $role);
                }

                $rows[] = [
                    'resource' => class_basename($row['model']),
                    'policy' => class_basename($row['policy']),
                    'ability' => $ability,
                    'role_results' => $roleResults,
                ];
            }
        }

        return $rows;
    }

    private function evaluateAbility(string $policyClass, string $modelClass, string $ability, string $role): bool
    {
        if (! class_exists($policyClass) || ! method_exists($policyClass, $ability)) {
            return false;
        }

        try {
            $policy = app($policyClass);
            $actor = new User(['id' => 1, 'role' => $role, 'name' => '权限测试']);
            $method = new ReflectionMethod($policyClass, $ability);
            $parameterCount = count($method->getParameters());

            if ($parameterCount <= 1) {
                return (bool) $policy->{$ability}($actor);
            }

            $target = $this->makeTargetModel($modelClass);

            return $target instanceof Model
                ? (bool) $policy->{$ability}($actor, $target)
                : false;
        } catch (\Throwable) {
            return false;
        }
    }

    private function makeTargetModel(string $modelClass): ?Model
    {
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        if ($modelClass === User::class) {
            return new User(['id' => 2, 'role' => 'user', 'name' => '目标用户']);
        }

        return new $modelClass();
    }
}
