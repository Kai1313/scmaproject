<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 3 -->
<script src="{{ asset('assets/bower_components/jquery/dist/jquery.min.js') }}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('assets/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let activeMenu = $('li.nav-item.active')
    activeMenu.parents('li').addClass('active')

    $("#tombol_ganti_password").click(function(e) {
        e.preventDefault()
        $('#cover-spin').show()
        let param = {
            password2: $("#password2").val(),
            token_pengguna: "{{ session('token') }}"
        };
        $.ajax({
            url: "{{ env('OLD_API_ROOT') }}actions/core/ganti_password.php",
            type: "POST",
            data: param,
            success: function(res) {
                if (res.length > 0) {
                    if (res[0].hasil > 0) {
                        $('#ganti_password').modal('hide')
                        $('#submit_ganti_password').find('input').each(function(i, v) {
                            $(v).val('')
                        })

                        Swal.fire('Berhasil Ubah Password', res[0].pesan_hasil, 'success')
                    } else {
                        Swal.fire('Gagal Ubah Password', res[0].pesan_hasil, 'error')
                    }
                }

                $('#cover-spin').hide()
            },
            error: function(error) {
                Swal.fire("Gagal Ubah Password. ", error.responseJSON.message, 'error')
                $('#cover-spin').hide()
            }
        });
    });
</script>
