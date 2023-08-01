@extends('layouts.main')
@section('addedStyles')
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables-responsive/css/responsive.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" />

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

        #exTab1 .tab-content {
            color: white;
            background-color: whitesmoke;
            padding: 15px 15px;
        }

        #exTab2 h3 {
            color: white;
            background-color: #428bca;
            padding: 5px 15px;
        }

        /* remove border radius for the tab */

        #exTab1 .nav-pills>li>a {
            border-radius: 0;
        }

        /* change border radius for the tab , apply corners on top*/

        #exTab3 .nav-pills>li>a {
            border-radius: 4px 4px 0 0;
        }

        #exTab3 .tab-content {
            color: white;
            background-color: #428bca;
            padding: 5px 15px;
        }

        .fc-center {
            color: black
        }

        .fc-day-number {
            color: black
        }

        .fc-day-header {
            background: #EBA925 !important;
        }

        .bg-danger {
            background: red !important;
        }

        .bg-danger-text {
            color: red !important;
        }

        .bg-secondary {
            background: rgb(172, 170, 170) !important;
        }

        .bg-secondary-text {
            color: rgb(172, 170, 170) !important;
        }

        .bg-success {
            background: rgb(11, 192, 20) !important;
        }

        .bg-success-text {
            color: rgb(11, 192, 20) !important;
        }


        .mt-3 {
            margin-top: 1rem;
            margin-left: 1rem;
        }

        .mr-3 {
            margin-right: 1rem;
        }
    </style>
@endsection

@section('header')
    <section class="content-header">
        <h1>
            Progress Visit
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Dashboard</a></li>
            <li class="active">Progress Visit</li>
        </ol>
    </section>
@endsection

@section('main-section')
    <div class="content container-fluid">
        <div class="box">
            <div class="box-header">
                <div class="row">
                    <div class="col-md-2 filter-div">
                        <label>Range Tanggal</label>
                        <div class="form-group">
                            <input type="text" id="daterangepicker" class="form-control"
                                value="{{ startOfMonth('d/m/Y') }} - {{ endOfMonth('d/m/Y') }}" />
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Cabang</label>
                        <div class="form-group">
                            <select id="id_cabang" class="form-control select2">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $branch)
                                    <option value="{{ $branch['id'] }}">{{ $branch['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label>Visualisasi Data</label>
                        <div class="form-group">
                            <select id="visualisasi_data" class="form-control select2" multiple onchange="filter()">
                                <option selected value="1">Perbandingan rencana visit</option>
                                <option selected value="2">Perbandingan metode visit</option>
                                <option selected value="3">Perbandingan report visit</option>
                                <option selected value="4">Perbandingan nilai order visit</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 filter-div">
                        <label class="d-block">&nbsp;</label>
                        <div class="form-group">
                            <button type="button" class="btn btn-info" onclick="filter()"><i class="fa fa-search"></i>
                                Cari</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="exTab1">
                    <ul class="nav nav-pills">
                        <li class="active">
                            <a href="#1a" data-toggle="tab">Grafik Visit</a>
                        </li>
                        <li><a href="#2a" data-toggle="tab" onclick="">Kalender Visit</a>
                        </li>
                    </ul>

                    <div class="tab-content clearfix">
                        <div class="tab-pane active" id="1a">
                            <div class="row">
                                <div class="col-md-6 mb-3 container-1 parent-container">
                                    <div id="perbandingan-visit">

                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 container-2 parent-container">
                                    <div id="perbandingan-realisasi-visit">

                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 container-3 parent-container">
                                    <div id="perbandingan-progress-visit">

                                    </div>
                                </div>
                                <div class="col-md-6 mb-3 container-4 parent-container">
                                    <div id="perbandingan-nilai-order-visit">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="2a" style="background: white">
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <span class="mr-3" style="color: black"><i class="fa fa-square bg-danger-text"></i>
                                        Batal
                                        Visit</span>
                                    <span class="mr-3" style="color: black"><i class="fa fa-square bg-secondary-text"></i>
                                        Belum Visit</span>
                                    <span class="mr-3" style="color: black"><i class="fa fa-square bg-success-text"></i>
                                        Sudah
                                        Visit</span>
                                </div>
                                <div class="col-md-12">
                                    <div id='calendar'></div>
                                </div>
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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js'></script>
@endsection

@section('externalScripts')
    <script>
        var dataCalendar = [];
        var calendar;
        $('.select2').select2();
        $('#daterangepicker').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        $(document).ready(function() {})


        function filter() {
            var data = $('#visualisasi_data').val();
            $('.parent-container').addClass('hidden');
            data.forEach(i => {
                $(`.container-${i}`).removeClass('hidden')
            });

            generateVisualisasiData();
        }

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pre_visit') }}",
            columns: [{
                data: 'visit_code',
                name: 'v.visit_code'
            }, {
                data: 'visit_date',
                name: 'v.visit_date'
            }, {
                data: 'nama_salesman',
                name: 's.nama_salesman',
            }, {
                data: 'nama_pelanggan',
                name: 'p.nama_pelanggan',
            }, {
                data: 'alamat_pelanggan',
                name: 'p.alamat_pelanggan',
            }, {
                data: 'pre_visit_desc',
                name: 'v.pre_visit_desc'
            }, {
                data: 'action',
                name: 'action',
                className: 'text-center',
                orderable: false,
                searchable: false
            }, ]
        });

        $(document).ready(function() {
            generateVisualisasiData();
        })

        function generateVisualisasiData() {
            $.ajax({
                url: '{{ route('generate-visualisasi-data-visit') }}',
                type: 'get',
                data: {
                    daterangepicker: function() {
                        return $("#daterangepicker").val();
                    },
                    id_cabang: function() {
                        return $("#id_cabang").val();
                    },
                    visualisasi_data: function() {
                        return $("#visualisasi_data").val();
                    }
                },
                success: function(res) {
                    Highcharts.chart(
                        'perbandingan-visit',
                        res.perbandingan_perencanaan_visit
                    );

                    Highcharts.chart(
                        'perbandingan-realisasi-visit',
                        res.perbandingan_metode_visit_ke_pelanggan
                    );

                    Highcharts.chart(
                        'perbandingan-progress-visit',
                        res.perbandingan_progress_visit_ke_pelanggan
                    );

                    Highcharts.chart(
                        'perbandingan-nilai-order-visit',
                        res.perbandingan_nilai_sales_order_visit
                    );
                    res.timeline.forEach(d => {
                        switch (d.status * 1) {
                            case 0:
                                var color = 'bg-danger';
                                break;
                            case 1:
                                var color = 'bg-secondary';
                                break;
                            case 2:
                                var color = 'bg-success';
                                break;
                            default:
                                break;
                        }
                        dataCalendar.push({
                            title: `Visit ke ${d.pelanggan.nama_pelanggan}`,
                            pelanggan: d.pelanggan.nama_pelanggan,
                            marketing: d.salesman.nama_salesman,
                            status: d.status,
                            color: color,
                            start: d.visit_date,
                        }, )
                    });
                    setTimeout(() => {
                        $.CalendarPage.init()
                    }, 3000);

                },
                error: function(error) {

                }
            })
        }
    </script>
    <script>
        ! function($) {

            var CalendarPage = function() {};

            CalendarPage.prototype.init = function() {

                    console.log(Date());
                    //checking if plugin is available
                    if ($.isFunction($.fn.fullCalendar)) {
                        /* initialize the calendar */

                        var date = new Date();
                        var d = date.getDate();
                        var m = date.getMonth();
                        var y = date.getFullYear();

                        calendar = $('#calendar').fullCalendar({
                            header: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'month,basicWeek,basicDay'
                            },
                            editable: false,
                            eventLimit: true, // allow "more" link when too many events
                            droppable: true, // this allows things to be dropped onto the calendar !!!
                            eventDurationEditable: false,
                            eventRender: function(copiedEventObject, element) {
                                var html =
                                    '<h5 style="padding-left:1rem;padding-right:1rem">' + copiedEventObject
                                    .title + '</h5>' +
                                    `<p style="padding-left:1rem;padding-right:1rem">Marketing atas nama ${copiedEventObject.marketing}</p>`;


                                // consoel.log(element);
                                element.find('.fc-title').html(html);
                                element.addClass(`d-flex ${copiedEventObject.color} py-1`);

                                // element.find(".fc-title").click(function() {
                                //     location.href = '/' +
                                //         copiedEventObject.slug;
                                // });
                            },
                            drop: function(date,
                                allDay) { // this function is called when something is dropped
                                // retrieve the dropped element's stored Event Object
                                var originalEventObject = $(this).data('eventObject');
                                // we need to copy it, so that multiple events don't have a reference to the same object
                                var copiedEventObject = $.extend({}, originalEventObject);

                                // assign it the date that was reported
                                copiedEventObject.start = date;
                                copiedEventObject.allDay = allDay;

                                // render the event on the calendar
                                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                                $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
                                store(copiedEventObject, date.format("YYYY-MM-DD"));
                            },
                            events: dataCalendar,
                        });

                        /*Add new event*/
                        // Form to add new event
                    } else {
                        alert("Calendar plugin is not installed");
                    }
                },
                //init
                $.CalendarPage = new CalendarPage,
                $.CalendarPage.Constructor = CalendarPage
        }
        (window.jQuery),


        //initializing
        function($) {

        }(window.jQuery);
    </script>
@endsection
