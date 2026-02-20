<?php

namespace App\DataTables;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TeacherDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('nama_lengkap', function($row) {
                return $row->user->name ?? '-';
            })
            ->addColumn('email_login', function($row) {
                return $row->user->email ?? '-';
            })
            ->addColumn('keterangan', function($row) {
                $html = '';
                if($row->major) {
                    $html .= '<span class="badge bg-info text-dark">Guru Jurusan '.($row->major->code ?? '').'</span>';
                } else {
                    $html .= '<span class="badge bg-secondary">Guru Umum</span>';
                }

                if($row->role_type == 'piket') {
                    $html .= '<div class="mt-1"><span class="badge bg-warning text-dark">Petugas Piket</span></div>';
                }
                return $html;
            })
            ->addColumn('action', function($row) {
                return '
                <div class="btn-group">
                    <a href="'.route('teachers.show', $row->id).'" class="btn btn-sm btn-success text-white"><i class="bx bx-info-circle"></i></a>
                    <button type="button" class="btn btn-sm btn-warning text-white btn-edit" data-id="'.$row->id.'"><i class="bx bx-message-square-edit"></i></button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'"><i class="bx bx-message-square-x"></i></button>
                </div>';
            })
            ->rawColumns(['keterangan', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Teacher $model): QueryBuilder
    {
        // Gunakan with() agar tidak terjadi N+1 query problem
        return $model->newQuery()->with(['user', 'major']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('teacher-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->parameters([
                        'language' => ['url' => '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json']
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(30),
            Column::computed('nama_lengkap')->title('Nama Lengkap'),
            Column::make('nip')->title('NIP')->addClass('text-center'),
            Column::make('gender')->title('L/P')->addClass('text-center'),
            Column::computed('email_login')->title('Email (Login)'),
            Column::make('phone')->title('No. HP'),
            Column::computed('keterangan')->title('Keterangan')->addClass('text-center'),
            Column::computed('action')->title('Aksi')->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Teacher_' . date('YmdHis');
    }
}
