<link rel="stylesheet" href="{{ asset('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/font-awesome/css/font-awesome.min.css') }}">
<!-- Ionicons -->
<link rel="stylesheet" href="{{ asset('assets/bower_components/Ionicons/css/ionicons.min.css') }}">

<style>
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .justify-content-center {
        justify-content: center;
    }

    .mr-1 {
        margin-right: 1rem;
    }

    .mr-2 {
        margin-right: 2rem;
    }

    .treeview-menu a.active {
        color: white !important;
    }

    .dropdown-icon {
        min-width: 20px !important;
    }

    .text-info>a {
        color: rgb(124, 233, 233) !important;
        font-weight: bold;
    }

    .text-success>a {
        color: rgb(81, 173, 81) !important;
        font-weight: bold;
    }

    .text-danger>a {
        color: rgb(238, 70, 70) !important;
        font-weight: bold;
    }

    .text-warning>a {
        color: purple !important;
        font-weight: bold;
    }

    .data-table td {
        vertical-align: middle !important;
    }

    .progressa {
        border-radius: 50px !important;
        height: 20px;
        font-size: 8px;
        overflow: hidden;
        height: 20px;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        line-height: 2;
        height: 20px;
        border: 3px solid transparent;
    }

    .progressab {
        background-image: -webkit-linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
        background-image: -o-linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
        -webkit-background-size: 40px 40px;
        background-size: 40px 40px;
        -webkit-transition: width .25s ease, height .25s ease, font-size .25s ease;
        -moz-transition: width .25s ease, height .25s ease, font-size .25s ease;
        -ms-transition: width .25s ease, height .25s ease, font-size .25s ease;
        -o-transition: width .25s ease, height .25s ease, font-size .25s ease;
        transition: width .25s ease, height .25s ease, font-size .25s ease;
        width: 0;
        color: #fff;
        text-align: center;
        font-family: 'Open Sans', sans-serif !important;
        animation: progress-bar-stripes 2s linear infinite reverse;
    }

    @keyframes progress-bar-stripes {
        0% {
            background-position: 40px 0;
        }

        100% {
            background-position: 0 0;
        }
    }

    .mr-3 {
        margin-right: 1rem;
    }
</style>
