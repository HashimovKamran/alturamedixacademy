<?php

namespace App\Http\Controllers\Admin;

use App\AlturaPageBuilder\Services\AlturaPageBuilderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageBuilderAssetController extends Controller
{
    public function __construct(private readonly AlturaPageBuilderService $builder) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->builder->assets($request->integer('page', 1), $request->integer('per_page', 36));
        return response()->json(['data' => $items]);
    }

    public function show(int $asset): JsonResponse { return response()->json(['data' => $this->builder->asset($asset)]); }
    public function store(Request $request): JsonResponse { $data=$request->validate(['file'=>['required','file','max:10240'],'alt_text'=>['nullable','string','max:255']]); return response()->json(['data'=>$this->builder->uploadAsset($data['file'],$data['alt_text']??null,$request->session()->get('admin_user_id'))],201); }
    public function update(Request $request,int $asset): JsonResponse { $data=$request->validate(['alt_text'=>['nullable','string','max:255']]); return response()->json(['data'=>$this->builder->updateAsset($asset,$data['alt_text']??null)]); }
    public function destroy(int $asset): JsonResponse { $this->builder->deleteAsset($asset); return response()->json(status:204); }
}
