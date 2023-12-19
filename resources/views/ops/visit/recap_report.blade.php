@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        ul.horizontal-list {
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        ul.horizontal-list li {
            display: inline;
        }

        .mb-1 {
            margin-bottom: .25rem !important;
        }

        th {
            text-align: center;
        }

        .head-checkbox {
            padding-top: 30px;
        }

        .head-checkbox label {
            margin-right: 10px;
        }

        .rounded-0 {
            border-radius: 0;
        }

        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Laporan Rekap Kunjungan
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Laporan Rekap Kunjungan</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select id="id_cabang" class="form-control select2" name="id_cabang">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Tanggal</label>
                        <div class="form-group">
                            <input type="text" id="daterangepicker" class="form-control" name="daterangepicker" />
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Sales</label>
                        <div class="form-group">
                            <select id="id_salesman" class="form-control select2" name="id_salesman">
                                <option value="">Semua Sales</option>
                                @foreach ($salesmans as $sales)
                                    <option value="{{ $sales->id }}">{{ $sales->text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label class="d-block">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-info btn-search"><i class="fa fa-search mr-1"></i>
                                Cari</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="target-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="main-data">
                        <thead>
                            <tr>
                                <th rowspan="2" width="110">Sales Name</th>
                                <th rowspan="2" width="110">Date</th>
                                <th rowspan="2" width="220">Customer</th>
                                <th colspan="{{ count($activities) }}">Activity</th>
                                <th rowspan="2">Description</th>
                            </tr>
                            <tr>
                                @foreach ($activities as $activity)
                                    <th width="50">{{ $activity }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Rekap</h3>
                    </div>
                    <div class="box-body">
                        <table id="recap-data"></table>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Grafik</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart">
                            <canvas id="barChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('addedScripts')
    <script src="{{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/bower_components/datatables-responsive/js/dataTables.responsive.js') }}"></script>
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ asset('assets/bower_components/chart.js/Chart.js') }}"></script>
@endsection

@section('externalScripts')
    <script>
        var salesman = {!! json_encode($salesmans) !!}
        var activities = {!! json_encode($activities) !!}
        $('.select2').select2();

        $('#daterangepicker').daterangepicker({
            timePicker: false,
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

        filterDatatable()

        function filterDatatable() {
            let param = {};
            $('.box-header').find('select,input').each(function(i, v) {
                param[$(v).attr('name')] = function() {
                    return $(v).val()
                }
            })

            return param;
        }

        function getData() {
            $('#cover-spin').show()
            $.ajax({
                url: '',
                data: filterDatatable(),
                success: function(res) {
                    if (res.result) {
                        $('#main-data').find('tbody').html(res.htmlMainData)
                        $('#recap-data').html(res.htmlRecapData)
                        // console.log(barChart)
                        barChart.data.datasets[0].data = [1, 2, 3, 4];
                        barChart.update();
                        // areaChartData.datasets[0].data = res.chartData.values
                        // areaChartData.data.datasets[i].data = res.chartData.values
                        // barChart.update()
                    }

                    $('#cover-spin').hide()
                },
                error: function(error) {
                    $('#cover-spin').hide()
                    Swal.fire("Gagal Ambil Data. ", error.responseJSON.message, 'error')
                }
            })
        }

        getData()

        $('.btn-search').click(function(e) {
            e.preventDefault()
            getData()
        })

        var areaChartData = {
            labels: activities,
            datasets: [{
                // fillColor: '#00a65a',
                // strokeColor: '#00a65a',
                // pointColor: '#00a65a',
                // pointStrokeColor: '#c1c7d1',
                // pointHighlightFill: '#fff',
                // pointHighlightStroke: 'rgba(220,220,220,1)',
                data: []
            }, ]
        }

        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChart = new Chart(barChartCanvas)
        var barChartData = areaChartData
        var barChartOptions = {
            //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
            scaleBeginAtZero: true,
            //Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines: true,
            //String - Colour of the grid lines
            scaleGridLineColor: 'rgba(0,0,0,.05)',
            //Number - Width of the grid lines
            scaleGridLineWidth: 1,
            //Boolean - Whether to show horizontal lines (except X axis)
            scaleShowHorizontalLines: true,
            //Boolean - Whether to show vertical lines (except Y axis)
            scaleShowVerticalLines: true,
            //Boolean - If there is a stroke on each bar
            barShowStroke: true,
            //Number - Pixel width of the bar stroke
            barStrokeWidth: 2,
            //Number - Spacing between each of the X value sets
            barValueSpacing: 5,
            //Number - Spacing between data sets within X values
            barDatasetSpacing: 1,
            //String - A legend template
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
            //Boolean - whether to make the chart responsive
            responsive: true,
            maintainAspectRatio: true
        }

        barChartOptions.datasetFill = false
        barChart.Bar(barChartData, barChartOptions)
    </script>
@endsection
