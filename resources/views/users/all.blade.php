
@section('title')
   Laporan Presensi Pembelajaran
@endsection
<x-app-layout>
    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
<style>
    .large-chexbox{
        transform: scale(1.5);
        /* margin-left: 2em; */
    }
</style>
<div class="page-content">
    <!--breadcrumb-->
    <div class="mb-3 page-breadcrumb d-none d-sm-flex align-items-center">
        <div class="breadcrumb-title pe-3">Jadwal Pelajaran</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="p-0 mb-0 breadcrumb">
                    <li class="breadcrumb-item"><a href="{{route('dashboard')}}"><i class="bx bx-home-alt"></i></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Data Jadwal Pelajaran</li>
                </ol>
            </nav>
        </div>
        
    </div>
    <!--end breadcrumb-->
    <div class="mb-3">
    <a href="" class="btn btn-primary" title="Import User" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="bx bx-import"></i> User</a>
        <a href="" class="btn btn-danger" title="Print Pengguna Siswa" target="_blank"><i class="lni lni-printer"></i> All</a>
        <a href="" class="btn btn-warning" title="Print Pengguna Guru" target="_blank"><i class="lni lni-printer"></i> Guru</a>
        <a href="" class="btn btn-primary" title="Print Pengguna Wakil" target="_blank"><i class="lni lni-printer"></i> Wakil</a>
        <!-- <a href="" class="btn btn-primary btn btn-sm"><i class="bx bx-printer"></i>Print</a> -->
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var jadwalId = $(this).data('jadwal');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "",
                method: "POST",
                data: {
                    jadwal : jadwalId,
                    is_checked: isChecked ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response){
                    toastr.success(response.message);
                },
                error: function(){

                }
            });

        });
    });

</script>
<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            var userId = $(this).data('user');
            var isChecked = $(this).is(':checked');

            // send an ajax request to update status

            $.ajax({
                url: "",
                method: "POST",
                data: {
                    user : userId,
                    is_checked: isChecked ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response){
                    toastr.success(response.message);
                },
                error: function(){

                }
            });

        });
    });
</script>

</x-app-layout>