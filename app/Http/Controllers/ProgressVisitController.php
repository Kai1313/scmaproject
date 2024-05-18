<?php

namespace App\Http\Controllers;

use App\Salesman;
use App\Visit;
use Illuminate\Http\Request;

class ProgressVisitController extends Controller
{

    public function index(Request $request)
    {
        if (checkUserSession($request, 'marketing-tool/progress-visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }

        $cabang = session()->get('access_cabang');

        return view('ops.progressVisit.index', [
            "pageTitle" => "SCA OPS | Progress Visit | index",
            'cabang' => $cabang,
        ]);
    }

    public function generateVisualisasiData(Request $req)
    {
        //Set Up Awal
        $dateAwal = dateStore(explode(' - ', $req->daterangepicker)[0]);
        $dateAkhir = dateStore(explode(' - ', $req->daterangepicker)[1]);
        $idSalesman = explode(',', $req->id_salesman);
        $marketing = Salesman::whereIn('id_salesman', $idSalesman)->get();

        $data['perbandingan_perencanaan_visit'] = [];
        $data['perbandingan_metode_visit_ke_pelanggan'] = [];
        $data['perbandingan_progress_visit_ke_pelanggan'] = [];
        $data['perbandingan_nilai_sales_order_visit'] = [];
        $data['perbandingan_kategori_pelanggan'] = [];

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

        $perbandinganVisit = [
            'Batal Visit',
            'Realisasi Visit',
            'Perencanaan Visit',
        ];

        $kategoriPelanggan = [
            'EXISTING CUSTOMER',
            'OLD CUSTOMER',
            'NEW CUSTOMER',
        ];

        $progress = [
            1,
            2,
            3,
        ];

        $tempSeriesPerbandinganVisit = [];
        foreach ($marketing as $key => $value) {
            $jumlahJadwalVisit = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '!=', '3')->count();
            $jumlahBatalJadwalVisit = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '0')->count();
            $jumlahRealisasiVisit = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '2')->count();
            $nilaiSalesOrder = $value->visit
                ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                ->where('progress_ind', 3)
                ->sum('total');
            $tempMarketing[$key][] = $value->nama_salesman;
            $tempMarketing[$key][] = $jumlahJadwalVisit;

            $tempTotalMarketing[$key][] = $value->nama_salesman;
            $tempTotalMarketing[$key][] = $nilaiSalesOrder;

            $namaMarketing[] = $value->nama_salesman;

            $tempSeriesPerbandinganVisit[] = [$jumlahBatalJadwalVisit, $jumlahRealisasiVisit, $jumlahJadwalVisit];
        }

        // cari perbandingan visit per sales
        $seriesPerbandinganVisit = [];
        foreach ($perbandinganVisit as $i => $d) {

            $tempSeriesPerbandinganVisit = [];
            foreach ($marketing as $key => $value) {
                if ($d == 'Batal Visit') {
                    $temp = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '0')->count();
                } elseif ($d == 'Realisasi Visit') {
                    $temp = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '2')->count();
                } elseif ($d == 'Perencanaan Visit') {
                    $temp = $value->visit->whereBetween('visit_date', [$dateAwal, $dateAkhir])->where('status', '!=', '3')->count();
                }
                $tempSeriesPerbandinganVisit[] = $temp;
            }
            $seriesPerbandinganVisit[] = [
                'name' => $d,
                'data' => $tempSeriesPerbandinganVisit,
            ];
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
        // cari kategori pelanggan
        $kategoriPelangganTemp = [];
        foreach ($kategoriPelanggan as $i => $d) {
            $kategoriPelangganTemp[] = [
                'name' => $d,
                'data' => [],
            ];

            foreach ($marketing as $key => $value) {
                $count = $value->visit
                    ->whereBetween('visit_date', [$dateAwal, $dateAkhir])
                    ->where('status_pelanggan', $d)
                    ->where('status', '!=', '3')
                    ->filter(function ($d) {
                        if (request('id_cabang') != '') {
                            return $d->id_cabang == request('id_cabang');
                        }

                        return true;
                    })
                    ->count();

                $kategoriPelangganTemp[$i]['data'][] = $count;
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

        //Perbandingan Perencanaan Visit
        $data['perbandingan_perencanaan_visit'] = [
            'chart' => [
                'type' => 'bar',
            ],
            'title' => [
                'text' => "Perbandingan jadwal visit dengan realisasi visit tanggal $dateAwal - $dateAkhir",
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'xAxis' => [
                'categories' => $namaMarketing,
                'labels' => [
                    'autoRotation' => [-45, -90],
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'yAxis' => [
                'min' => 0,
                'title' => [
                    'text' => 'Perbandingan dan realisasi',
                    'style' => [
                        'fontSize' => '12px',
                        'fontFamily' => 'Verdana, sans-serif',
                    ],
                ],
            ],
            'tooltip' => [
                "style" => ["fontSize" => "12px"],
            ],
            'legend' => [
                'reversed' => true,
                'itemStyle' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'Verdana, sans-serif',
                ],
            ],
            'colors' => [
                '#ff0808',
                '#08ddff',
                '#00ff7e',
            ],
            'plotOptions' => [
                'column' => [
                    'colorByPoint' => true,
                ],
                'series' => [
                    'stacking' => 'normal',
                    'dataLabels' => [
                        'enabled' => true,
                        'style' => [
                            'fontSize' => '12px',
                            'fontFamily' => 'Verdana, sans-serif',
                        ],
                    ],
                ],
            ],
            'series' => $seriesPerbandinganVisit,
        ];
        //Perbandingan realisasi Visit

        $data['perbandingan_metode_visit_ke_pelanggan'] = [
            'chart' => [
                'type' => 'bar',
            ],
            'title' => [
                'text' => "Metode visit ke pelanggan tanggal $dateAwal - $dateAkhir",
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
                'type' => 'bar',
            ],
            'title' => [
                'text' => "Progress visit ke pelanggan tanggal $dateAwal - $dateAkhir",
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
            'series' => $progressTemp,
        ];
        // cari nilai sales order per pelanggan
        $data['perbandingan_nilai_sales_order_visit'] = [
            'chart' => [
                'type' => 'bar',
            ],
            'title' => [
                'text' => "Nilai sales order tanggal $dateAwal - $dateAkhir",
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
        //Perbandingan realisasi Visit
        $data['perbandingan_kategori_pelanggan'] = [
            'chart' => [
                'type' => 'bar',
            ],
            'title' => [
                'text' => "Perbandingan kategori pelanggan tanggal $dateAwal - $dateAkhir",
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
                    'text' => 'Jumlah Kategori Pelanggan',
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
            'series' => $kategoriPelangganTemp,
        ];

        // $data['timeline'] = Visit::select('id', 'id_salesman', 'visit_date', 'id_pelanggan', 'status')
        //     ->whereIn('id_salesman', $idSalesman)
        //     ->where(function ($q) {
        //         if (request('id_cabang') != '') {
        //             $q->where('id_cabang', request('id_cabang'));
        //         }
        //     })
        //     ->orderBy('visit_date', 'DESC')
        //     ->with(['pelanggan', 'salesman'])
        //     ->take(1000)
        //     ->get();

        return response()->json($data);
    }

    public function getData(Request $req)
    {

        $data = Visit::with(['salesman', 'pelanggan', 'cabang', 'sales_order'])->find($req->id);

        return response()->json($data);
    }

    public function getCalendar(Request $req)
    {
        $idSalesman = explode(',', $req->id_salesman);

        $data = Visit::select('id', 'id_salesman', 'visit_date', 'id_pelanggan', 'status')
            ->where('status', '!=', '3')
            ->whereIn('id_salesman', $idSalesman)
            ->where(function ($q) {
                if (request('id_cabang') != '') {
                    $q->where('id_cabang', request('id_cabang'));
                }
            })
            ->orderBy('visit_date', 'DESC')
            ->with(['pelanggan', 'salesman'])
            ->take(1000)
            ->get();

        return view('ops.progressVisit.calendar', compact('data'));
    }
}
