<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreWebsiteRequest;
use App\Http\Requests\API\UpdateWebsiteRequest;
use App\Http\Resources\WebsiteResource;
use App\Website;
use App\Traits\WebsiteTrait;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    use WebsiteTrait;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = ($request->input('sort') == 'desc' ? 'desc' : 'asc');
        $perPage = (($request->input('per_page') >= 10 && $request->input('per_page') <= 100) ? $request->input('per_page') : config('settings.paginate'));

        return WebsiteResource::collection(Website::where('user_id', $request->user()->id)
            ->when($search, function($query) use ($search) {
                return $query->searchUrl($search);
            })
            ->orderBy('url', $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWebsiteRequest $request
     * @return WebsiteResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreWebsiteRequest $request)
    {
        $created = $this->websiteStore($request);

        if ($created) {
            return WebsiteResource::make($created);
        }

        return response()->json([
            'message' => 'Resource not found.',
            'status' => 404
        ], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return WebsiteResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $link = Website::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($link) {
            return WebsiteResource::make($link);
        }

        return response()->json([
            'message' => 'Resource not found.',
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWebsiteRequest $request
     * @param int $id
     * @return WebsiteResource
     */
    public function update(UpdateWebsiteRequest $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $updated = $this->websiteUpdate($request, $website);

        if ($updated) {
            return WebsiteResource::make($updated);
        }

        return response()->json(404, [
            'message' => 'Resource not found.',
            'status' => 404
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        $website = Website::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($website) {
            $website->delete();

            return response()->json([
                'id' => $website->id,
                'object' => 'website',
                'deleted' => true,
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'Resource not found.',
            'status' => 404
        ], 404);
    }
}
