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
        // Script untuk Delete & Ajax Edit bisa diletakkan di sini menggunakan event delegation
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            // Tambahkan logika SweetAlert Delete di sini
        });
    </script>
</x-app-layout>