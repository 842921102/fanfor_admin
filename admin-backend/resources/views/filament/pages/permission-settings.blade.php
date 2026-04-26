<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">权限总览</x-slot>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-xs text-gray-500 dark:text-gray-400">角色数量</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ count($this->roleRows) }}</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Policy 数量</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ count($this->policyRows) }}</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                    <div class="text-xs text-gray-500 dark:text-gray-400">能力类型</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ count($this->abilityHeaders) }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">角色说明（AppRole）</x-slot>

            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="w-full table-auto divide-y divide-gray-200 text-start text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th
                                scope="col"
                                class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200"
                            >
                                角色值
                            </th>
                            <th
                                scope="col"
                                class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200"
                            >
                                中文说明
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($this->roleRows as $row)
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="px-4 py-3 font-mono text-xs text-gray-950 dark:text-white">
                                    {{ $row['value'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $row['label'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Policy 能力明细</x-slot>

            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="w-full table-auto divide-y divide-gray-200 text-start text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th
                                scope="col"
                                class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200"
                            >
                                模型
                            </th>
                            <th
                                scope="col"
                                class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200"
                            >
                                Policy 类
                            </th>
                            <th
                                scope="col"
                                class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200"
                            >
                                能力方法
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse ($this->policyRows as $row)
                            <tr class="bg-white align-top dark:bg-gray-900">
                                <td class="px-4 py-3 font-mono text-xs text-gray-950 dark:text-white">
                                    {{ $row['model'] }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-950 dark:text-white">
                                    {{ $row['policy'] }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    @if (count($row['abilities']) > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($row['abilities'] as $ability)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-white/10 dark:text-gray-200"
                                                >
                                                    {{ $ability }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">无公开能力方法</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    class="px-4 py-8 text-center text-gray-500 dark:text-gray-400"
                                    colspan="3"
                                >
                                    未读取到已注册 Policy。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">角色权限矩阵（只读）</x-slot>

            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="w-full min-w-[900px] table-auto divide-y divide-gray-200 text-start text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">资源</th>
                            <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">Policy</th>
                            <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">能力</th>
                            @foreach ($this->roleRows as $role)
                                <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-200">{{ $role['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse ($this->matrixRows as $row)
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="px-4 py-3 font-mono text-xs text-gray-950 dark:text-white">{{ $row['resource'] }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-950 dark:text-white">{{ $row['policy'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-white/10 dark:text-gray-200">
                                        {{ $row['ability'] }}
                                    </span>
                                </td>
                                @foreach ($this->roleRows as $role)
                                    @php($ok = (bool) ($row['role_results'][$role['value']] ?? false))
                                    <td class="px-4 py-3">
                                        @if ($ok)
                                            <span class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">允许</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">禁止</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-gray-500 dark:text-gray-400" colspan="{{ 3 + count($this->roleRows) }}">
                                    暂无可展示的权限矩阵数据。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
