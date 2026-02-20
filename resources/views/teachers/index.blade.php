@section('title')
    Data Guru
@endsection

<x-app-layout>
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <div class="page-content">
        <div class="card shadow border-0">
            <div class="card-body">
                <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table align-middle table-striped table-bordered w-100']) }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        // // Script untuk Delete & Ajax Edit bisa diletakkan di sini menggunakan event delegation
        // $(document).on('click', '.btn-delete', function() {
        //     let id = $(this).data('id');
        //     // Tambahkan logika SweetAlert Delete di sini
        // });

        $(document).ready(function() {
    // JANGAN GUNAKAN: $('.btn-edit').click(function() { ... });
    
    // GUNAKAN DELEGASI SEPERTI INI:
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        console.log("ID Guru yang akan diedit:", id);
        
        // Contoh: Buka modal secara manual
        $('#editTeacherModal' + id).modal('show');
    });

    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data guru dan akun login akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit form hapus secara manual atau via AJAX
                $(this).closest('form').submit();
            }
        });
    });
});
    </script>
</x-app-layout>