@extends('layout')
@section('title')
<?= get_label('expense_types', 'Expense types') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{url('home')}}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{url('expenses')}}"><?= get_label('expenses', 'Expenses') ?></a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('expenses_types', 'Expense types') ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_expense_type_modal"><button type="button" class="btn btn-sm btn-primary action_create_expense_types" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title=" <?= get_label('create_expense_type', 'Create expense type') ?>"><i class="bx bx-plus"></i></button></a>
            <a href="{{url('expenses')}}"><button type="button" class="btn btn-sm btn-primary action_manage_expenses" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('expenses', 'Expenses') ?>"><i class='bx bx-list-ul'></i></button></a>
        </div>
    </div>
    @if ($expense_types > 0)
    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <input type="hidden" id="data_type" value="expense-types">
                <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ url('/expenses/expense-types-list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-query-params="queryParamsExpenseTypes">
                    <thead>
                        <tr>
                            <th data-checkbox="true"></th>
                            <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                            <th data-sortable="true" data-field="title"><?= get_label('title', 'Title') ?></th>
                            <th data-sortable="true" data-field="description" data-visible="false"><?= get_label('description', 'Description') ?></th>
                            <th data-sortable="true" data-field="created_at" data-visible="false"><?= get_label('created_at', 'Created at') ?></th>
                            <th data-sortable="true" data-field="updated_at" data-visible="false"><?= get_label('updated_at', 'Updated at') ?></th>
                            <th data-field="actions"><?= get_label('actions', 'Actions') ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @else
    <?php
    $type = 'Expense types'; ?>
    <x-empty-state-card :type="$type" />
    @endif
</div>
<script>
    var label_update = '<?= get_label('update', 'Update') ?>';
    var label_delete = '<?= get_label('delete', 'Delete') ?>';
</script>
<script src="{{asset('assets/js/pages/expenses.js')}}"></script>
@endsection