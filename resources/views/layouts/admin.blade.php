@php
    $users=\Auth::user();
    $currantLang = $users->currentLanguage();
    $languages=\App\Models\Utility::languages();
    $footer_text=isset(\App\Models\Utility::settings()['footer_text']) ? \App\Models\Utility::settings()['footer_text'] : '';
    $setting = App\Models\Utility::colorset();
    $color = (!empty($setting['color'])) ? $setting['color'] : 'theme-3';
    $SITE_RTL = !empty($setting['SITE_RTL'] ) ? $setting['SITE_RTL']  : 'off';

@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $SITE_RTL == 'on'?'rtl':''}}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
@include('partials.admin.head')
<body class="{{ $color }}">

    @include('partials.admin.menu')
    <div class="main-content position-relative">
        @include('partials.admin.header')
        <div class="page-content">
            @include('partials.admin.content')
        </div>

    </div>


<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleOverModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            </div>
        </div>
    </div>
</div>



@include('partials.admin.footer')
@include('Chatify::layouts.footerLinks')

@if(Session::has('success'))
    <script>
        toastrs('{{__('Success')}}', '{!! session('success') !!}', 'success');
    </script>
    {{ Session::forget('success') }}
@endif
@if(Session::has('error'))
    <script>
        toastrs('{{__('Error')}}', '{!! session('error') !!}', 'error');
    </script>
    {{ Session::forget('error') }}
@endif
<script>


    var exampleModal = document.getElementById('exampleModal')

    exampleModal.addEventListener('show.bs.modal', function(event) {
        // Button that triggered the modal
        var button = event.relatedTarget
            // Extract info from data-bs-* attributes
        var recipient = button.getAttribute('data-bs-whatever')
        var url = button.getAttribute('data-url')
        var size = button.getAttribute('data-size');
        var modalTitle = exampleModal.querySelector('.modal-title')
        var modalBodyInput = exampleModal.querySelector('.modal-body input')
                modalTitle.textContent = recipient
        $("#exampleModal .modal-dialog").addClass('modal-' + size);
        $.ajax({
            url: url,
            success: function (data) {
                $('#exampleModal .modal-body').html(data);
                $("#exampleModal").modal('show');
            },
            error: function (data) {
                data = data.responseJSON;
                toastrs('Error', data.error, 'error')
            }
        });
    })

    var exampleOverModal = document.getElementById('exampleOverModal')

    exampleOverModal.addEventListener('show.bs.modal', function(event) {
        // Button that triggered the modal
        var button = event.relatedTarget
            // Extract info from data-bs-* attributes
        var recipient = button.getAttribute('data-bs-whatever')
        var url = button.getAttribute('data-url')
        var size = button.getAttribute('data-size');
        var modalTitle = exampleOverModal.querySelector('.modal-title')
        var modalBodyInput = exampleOverModal.querySelector('.modal-body input')
                modalTitle.textContent = recipient
        $("#exampleOverModal .modal-dialog").addClass('modal-' + size);
        $.ajax({
            url: url,
            success: function (data) {
                $("#exampleOverModal").modal('hide');

                $('#exampleOverModal .modal-body').html(data);
                $("#exampleOverModal").modal('show');
            },
            error: function (data) {
                data = data.responseJSON;
                toastrs('Error', data.error, 'error')
            }
        });
    })


    function arrayToJson(form) {
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function(n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

$(document).on('click', '.fc-daygrid-event', function(e) {
    // if (!$(this).hasClass('project')) {
    e.preventDefault();
    var event = $(this);
    var title = $(this).find('.fc-content .fc-title').html();
    var size = 'md';
    var url = $(this).attr('href');
    $("#exampleModal .modal-title").html(title);
    $("#exampleModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        success: function(data) {
            $('#exampleModal .modal-body').html(data);
            $("#exampleModal").modal('show');

        },
        error: function(data) {
            data = data.responseJSON;
            toastrs('Error', data.error, 'error')
        }
    });
    // }
});



  </script>

    <footer class="dash-footer">
        <div class="footer-wrapper">
            <div class="py-1">
                <span class="text-muted"> {{ $footer_text }}</span>
            </div>
        </div>
    </footer>


</body>
</html>

