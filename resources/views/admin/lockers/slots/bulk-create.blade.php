@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bulkCreate.css') }}">
@endpush
<div class="d-grid d-lg-flex d-md-flex action-bar">
    <div id="table-actions" class="flex-grow-1 align-items-center">
        <x-forms.button-primary
            icon="save"
            class="mb-2"
            id="saveBulkCreate"
        >
            @lang('app.save')
            @lang('app.locker')
        </x-forms.button-primary>
    </div>
</div>
<div class="card bg-white border-0 b-shadow-4">
    <div class="card-header bg-white border-0 text-capitalize d-flex justify-content-between pt-4">
        <h4 class="box-title">{{ __('modules.lockers.tabs.bulkCreate') }}</h4>
    </div>
    <div class="card-body pt-2">
        @include('admin.lockers.slots._select_row_layout')
        <div id="bulkCreate"></div>
    </div>
</div>
<script src="{{ asset('js/bulkCreate.js') }}"></script>
<script>
    $(function () {
        const myLocker = $('#bulkCreate').bulkCreate({
            lockerId: {{ $locker->id }},
            modules: @json($modules),
        }).init();
    });
</script>
