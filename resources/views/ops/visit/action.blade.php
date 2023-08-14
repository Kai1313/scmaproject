<div class="dropdown">
    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
        aria-expanded="true">
        <i class="fa fa-align-justify"></i>
    </button>
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
        @if ($data->status == 1)
            <li role="presentation">
                <a role="menuitem" tabindex="-1" href="{{ route('visit-entry', [$data->id]) }}">
                    <i class="fa fa-check-square-o dropdown-icon"></i>Check In
                </a>
            </li>
        @endif
        @if ($data->status == 2)
            <li role="presentation">
                <a role="menuitem" tabindex="-1" href="{{ route('visit.reporting.show', [$data->id]) }}">
                    <i class="fa fa-edit dropdown-icon"></i>Report Visit
                </a>
            </li>
        @endif
    </ul>
</div>
