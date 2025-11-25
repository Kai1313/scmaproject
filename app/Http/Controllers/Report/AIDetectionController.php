<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AIDetection;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AIDetectionController extends Controller
{
    public function submitAIDetection(Request $request)
    {
        $filename     = $request->input('filename');
        $imageFormat  = $request->input('image_format');
        $imageData    = $request->input('image_data');
        $detectedName = $request->input('detected_name');
        $cctvName     = $request->input('cctv_name');

        // Validate required fields
        if (! $imageData || ! $filename || ! $imageFormat) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing required fields: filename, image_format, or image_data',
            ], 400);
        }

        try {
            // Remove data:image/jpeg;base64, prefix if exists
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);

            // Decode base64
            $decodedImage = base64_decode($imageData);

            if ($decodedImage === false) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid base64 image data',
                ], 400);
            }

            // Create directory if it doesn't exist
            $uploadDir = public_path('asset/ai_detections');
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Create full file path
            $filePath = $uploadDir . '/' . $filename . '.' . $imageFormat;

            // Save the file
            if (file_put_contents($filePath, $decodedImage) === false) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to save image file',
                ], 500);
            }

            // Optional: Save to database
            $aiDetection = AIDetection::create([
                'cctv_name'     => $cctvName,
                'detected_name' => $detectedName,
                'path'          => 'asset/ai_detections/' . $filename . '.' . $imageFormat,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'AI Detection image saved successfully',
                'data'    => $aiDetection,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error processing image: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // if (checkUserSession($request, 'ai_detection', 'show') == false) {
        //     return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        // }

        if ($request->ajax()) {
            return $this->getData($request, 'datatable');
        }

        return view('report_ops.aiDetection.index', [
            "pageTitle" => "SCA OPS | AI Detection | List",
        ]);
    }

    public function getData($request, $type)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $datas = AIDetection::select('ai_detections.*');
        if ($startDate && $endDate) {
            $datas = $datas->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $datas = $datas->orderBy('created_at', 'desc');

        if ($type == 'datatable') {
            $datatable = Datatables::of($datas);
            $datatable = $datatable->editColumn('created_at', function ($row) {
                return date('d/m/Y H:i:s', strtotime($row->created_at));
            })->editColumn('path', function ($row) {
                return '<button class="btn btn-primary btn-sm btn-preview-image"
                        data-image-url="' . asset($row->path) . '">
                        <i class="fa fa-image"></i> Preview
                    </button>';
            });

            $datatable = $datatable->rawColumns(['path'])->make(true);
            return $datatable;
        }

        $datas = $datas->get();
        return $datas;
    }
}
