<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SelectStatsRequest;
use App\Http\Resources\StatResource;
use App\Stat;
use App\Website;

class StatController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param SelectStatsRequest $request
     * @param $id
     * @return StatResource|\Illuminate\Http\JsonResponse
     */
    public function show(SelectStatsRequest $request, $id)
    {
        $website = Website::where('id', $id)->first();

        if ($website) {
            $search = $request->input('search');
            if ($request->input('sort') == 'min') {
                $sort = ['count', 'asc', 'min'];
            } else {
                $sort = ['count', 'desc', 'max'];
            }
            $perPage = (($request->input('per_page') >= 10 && $request->input('per_page') <= 100) ? $request->input('per_page') : config('settings.paginate'));

            $stat = Stat::selectRaw('`value`, SUM(`count`) as `count`')
                ->where([['website_id', '=', $website->id], ['name', '=', $request->input('name')]])
                ->when($search, function($query) use ($search) {
                    return $query->searchValue($search);
                })
                ->whereBetween('date', [$request->input('from'), $request->input('to')])
                ->groupBy('value')
                ->orderBy($sort[0], $sort[1])
                ->paginate($perPage)
                ->appends(['name' => $request->input('name'), 'search' => $search, 'sort' => $sort[2], 'per_page' => $perPage, 'from' => $request->input('from'), 'to' => $request->input('to')]);

            return StatResource::make($stat);
        }

        return response()->json([
            'message' => 'Resource not found.',
            'status' => 404
        ], 404);
    }
}
