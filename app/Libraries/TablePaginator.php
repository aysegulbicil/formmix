<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

final class TablePaginator
{
    public const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    public static function state(object $request, string $key = 'table', int $defaultPerPage = 10): array
    {
        $pageParam = $key === 'table' ? 'page' : $key . '_page';
        $perPageParam = $key === 'table' ? 'per_page' : $key . '_per_page';
        $perPage = (int) $request->getGet($perPageParam);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = $defaultPerPage;
        }

        return [
            'key' => $key,
            'page' => max(1, (int) $request->getGet($pageParam)),
            'perPage' => $perPage,
            'pageParam' => $pageParam,
            'perPageParam' => $perPageParam,
        ];
    }

    public static function paginateModel(Model $model, array $state): array
    {
        $rows = $model->paginate($state['perPage'], $state['key'], $state['page']);
        $total = (int) $model->pager->getTotal($state['key']);

        return [$rows, self::metadata($state, $total)];
    }

    public static function paginateBuilder(BaseBuilder $builder, array $state): array
    {
        $total = (int) (clone $builder)->countAllResults();
        $pagination = self::metadata($state, $total);
        $rows = $builder
            ->limit($pagination['perPage'], $pagination['offset'])
            ->get()
            ->getResultArray();

        return [$rows, $pagination];
    }

    public static function paginateArray(array $rows, array $state): array
    {
        $pagination = self::metadata($state, count($rows));

        return [
            array_slice($rows, $pagination['offset'], $pagination['perPage']),
            $pagination,
        ];
    }

    public static function metadata(array $state, int $total): array
    {
        $pageCount = max(1, (int) ceil($total / $state['perPage']));
        $page = min(max(1, (int) $state['page']), $pageCount);
        $offset = ($page - 1) * $state['perPage'];

        return array_merge($state, [
            'page' => $page,
            'total' => $total,
            'pageCount' => $pageCount,
            'offset' => $offset,
            'first' => $total === 0 ? 0 : $offset + 1,
            'last' => min($total, $offset + $state['perPage']),
            'options' => self::PER_PAGE_OPTIONS,
        ]);
    }
}
