<?php

namespace App\Http\Controllers;

use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;

class ProgressVisitController extends Controller
{

    function index(Request $request)
    {
        if (checkUserSession($request, 'progress_visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $cabang = session()->get('access_cabang');


        return view('ops.progressVisit.index', [
            "pageTitle" => "SCA OPS | Progress Visit | index",
            'cabang' => $cabang,
        ]);
    }

    function generateVisualisasiData(Request $req)
    {
        //Set Up Awal
        $dateAwal = dateStore(explode(' - ', $req->daterangepicker)[0]);
        $dateAkhir = dateStore(explode(' - ', $req->daterangepicker)[1]);

        $marketing = Salesman::all();

        $data['perbandingan_perencanaan_visit'] = [];
        $data['perbandingan_metode_visit_ke_pelanggan'] = [];
        $data['perbandingan_progress_visit_ke_pelanggan'] = [];
        $data['perbandingan_nilai_sales_order_visit'] = [];

        //Perbandingan Perencanaan Visit
        $data['perbandingan_perencanaan_visit']['chart'] = ['type' => 'column'];
        $data['perbandingan_perencanaan_visit']['title'] = [
            'text' => "Perbandingan perencanaan visit tanggal $dateAwal - $dateAkhir",
            'style' => [
                'fontSize' => '12px',
                'fontFamily' => 'Verdana, sans-serif',
            ],
        ];
        $data['perbandingan_perencanaan_visit']['xAxis'] = [
            'type' => 'category',
            'labels' => [
                'autoRotation' => [-45, -90],
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ]
            ],
        ];
        $data['perbandingan_perencanaan_visit']['yAxis'] = [
            'min' => 0,
            'title' => [
                'text' => 'Jumlah Visit',
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
        ];
        $data['perbandingan_perencanaan_visit']['legend'] = [
            'enabled' => false
        ];
        $data['perbandingan_perencanaan_visit']['tooltip'] = [
            "pointFormat" => "Jumlah Visit: <b>{point.y:.1f}</b>",
            "style" => ["fontSize" => "12px"],
        ];

        $tempMarketing = [];
        $tempTotalMarketing = [];
        $namaMarketing = [];

        $metode = [
            'LOKASI',
            'WHATSAPP',
            'TELEPON',
            'BATAL',
            'BELUM VISIT',
        ];

        $progress = [
            1,
            2,
            3,
        ];

        foreach ($marketing as $key => $value) {
            $jumlahJadwalVisit = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->count();
            $nilaiSalesOrder = $value->visit
                ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                ->where('progress_ind', 3)
                ->sum('total');
            $tempMarketing[$key][] = $value->nama_salesman;
            $tempMarketing[$key][] = $jumlahJadwalVisit;

            $tempTotalMarketing[$key][] = $value->nama_salesman;
            $tempTotalMarketing[$key][] = $nilaiSalesOrder;

            $namaMarketing[] = $value->nama_salesman;
        }

        // cari metode per pelanggan
        $metodeTemp = [];
        foreach ($metode as $i => $d) {
            $metodeTemp[] = [
                'name' => $d,
                'data' => [],
            ];

            foreach ($marketing as $key => $value) {
                if ($d == 'BATAL') {
                    $count = $value->visit
                        ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                        ->where('status', 0)
                        ->filter(function ($d) {
                            if (request('id_cabang') != '') {
                                return $d->id_cabang == request('id_cabang');
                            }

                            return true;
                        })
                        ->count();

                    $metodeTemp[$i]['data'][] = $count;
                } else if ($d == 'BELUM VISIT') {
                    $count = $value->visit
                        ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                        ->where('status', 1)
                        ->filter(function ($d) {
                            if (request('id_cabang') != '') {
                                return $d->id_cabang == request('id_cabang');
                            }

                            return true;
                        })
                        ->count();
                    $metodeTemp[$i]['data'][] = $count;
                } else {
                    $count = $value->visit
                        ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                        ->where('status', 2)
                        ->where('visit_type', $d)
                        ->filter(function ($d) {
                            if (request('id_cabang') != '') {
                                return $d->id_cabang == request('id_cabang');
                            }

                            return true;
                        })
                        ->count();

                    $metodeTemp[$i]['data'][] = $count;
                }
            }
        }
        // cari progress per pelanggan
        $progressTemp = [];
        foreach ($progress as $i => $d) {
            $progressTemp[] = [
                'name' => Visit::$progressIndicator[$d],
                'data' => [],
            ];

            foreach ($marketing as $key => $value) {
                $count = $value->visit
                    ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                    ->where('status', 2)
                    ->where('progress_ind', $d)
                    ->count();

                $progressTemp[$i]['data'][] = $count;
            }
        }


        $data['perbandingan_perencanaan_visit']['series'] = [
            [
                'name' => 'Perbandingan Perencanaan Visit',
                'colorByPoint' => true,
                'groupPadding' => 0,
                'data' => $tempMarketing,
                'dataLabels' => [
                    'enabled' => true,
                    'rotation' => -90,
                    'color' => '#FFFFFF',
                    'align' => 'right',
                    'format' => '{point.y}',
                    'y' => 10,
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ]
        ];
        //Perbandingan realisasi Visit

        $data['perbandingan_metode_visit_ke_pelanggan'] = [
            'chart' => [
                'type' => 'column',
            ],
            'title' => [
                'text' => "Perbandingan metode visit ke pelanggan tanggal $dateAwal - $dateAkhir",
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'xAxis' => [
                'categories' => $namaMarketing,
                'crosshair' => true,
                'accessibility' => [
                    'description' => 'Jumlah Progress Visit',
                ],
                'labels' => [
                    'autoRotation' => [
                        0 => -45,
                        1 => -90,
                    ],
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Jumlah Metode Visit',
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'legend' => [
                'enabled' => true,
                'itemStyle' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'tooltip' => [
                'style' => [
                    'fontSize' => '12px',
                ],
            ],
            'series' => $metodeTemp,
        ];
        // cari progress visit per pelanggan
        $data['perbandingan_progress_visit_ke_pelanggan'] = [
            'chart' => [
                'type' => 'column',
            ],
            'title' => [
                'text' => "Perbandingan progress visit ke pelanggan tanggal $dateAwal - $dateAkhir",
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'xAxis' => [
                'categories' => $namaMarketing,
                'crosshair' => true,
                'accessibility' => [
                    'description' => 'Jumlah Progress Visit',
                ],
                'labels' => [
                    'autoRotation' => [
                        0 => -45,
                        1 => -90,
                    ],
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Jumlah Progress Visit',
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'legend' => [
                'enabled' => true,
                'itemStyle' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'tooltip' => [
                'style' => [
                    'fontSize' => '12px',
                ],
            ],
            'series' => $progressTemp
        ];
        // cari nilai sales order per pelanggan
        $data['perbandingan_nilai_sales_order_visit'] = [
            'chart' => [
                'type' => 'column',
            ],
            'title' => [
                'text' => "Perbandingan nilai sales order tanggal $dateAwal - $dateAkhir",
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'xAxis' => [
                'type' => 'category',
                'labels' => [
                    'autoRotation' => [
                        0 => -45,
                        1 => -90,
                    ],
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Jumlah Nilai Order',
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'legend' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'style' => [
                    'fontSize' => '12px',
                ],
            ],
            'series' => [
                [
                    'name' => 'Perbandingan nilai sales order',
                    'colorByPoint' => true,
                    'groupPadding' => 0,
                    'data' => $tempTotalMarketing,
                ],
            ],
        ];

        $data['timeline'] = Visit::select('id', 'id_salesman', 'visit_date', 'id_pelanggan', 'status')
            ->where(function ($q) {
                if (request('id_cabang') != '') {
                    $q->where('id_cabang', request('id_cabang'));
                }
            })
            ->orderBy('visit_date', 'DESC')
            ->with(['pelanggan', 'salesman'])
            ->take(1000)
            ->get();

        return response()->json($data);
    }
}
