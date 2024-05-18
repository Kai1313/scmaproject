<div id='calendar'></div>

<script>
    var dataCalendar = [];
    var calendar;

    @foreach ($data as $d)
        switch ('{{ $d->status }}' * 1) {
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
            id: `{{ $d->id }}`,
            title: `Visit ke {{ $d->pelanggan->nama_pelanggan }}`,
            pelanggan: '{{ $d->pelanggan->nama_pelanggan }}',
            marketing: '{{ $d->salesman->nama_salesman }}',
            status: '{{ $d->status }}',
            color: color,
            start: '{{ $d->visit_date }}',
        }, )
    @endforeach
    console.log(calendar);
    $.CalendarPage.init();

    ! function($) {

        function fullCalendarOption() {
            return {
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

                    element.find(".fc-title").click(function() {
                        modalOpen(copiedEventObject.id);
                    });
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
            }
        }

        var CalendarPage = function() {};

        CalendarPage.prototype.reset = function() {
            if ($.isFunction($.fn.fullCalendar)) {
                $('#calendar').fullCalendar('removeEvents');

                $('#calendar').fullCalendar('renderEvent', fullCalendarOption());
            }
        }

        CalendarPage.prototype.init = function() {
                //checking if plugin is available
                if ($.isFunction($.fn.fullCalendar)) {
                    /* initialize the calendar */

                    var date = new Date();
                    var d = date.getDate();
                    var m = date.getMonth();
                    var y = date.getFullYear();

                    calendar = $('#calendar').fullCalendar(
                        fullCalendarOption()
                    );

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
