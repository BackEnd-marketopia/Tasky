@extends('layout')
@section('title')
<?= get_label('project_mind_map_view', 'Project Mind Map View') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{url('home')}}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{url(getUserPreferences('projects', 'default_view'))}}"><?= get_label('projects', 'Projects') ?></a></li>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('projects.info',['id' =>$project->id]) }}">{{ $project->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ get_label('mind_map_view', 'Mind Map View') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="alert alert-primary" role="alert">
        <i class="bx bx-info-circle"></i> 
        {{ get_label('mind_map_collapse_info', 'Click the yellow dots to expand or collapse sections of the mind map.') }}
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mind-map-container" id="mind-map"></div>
        </div>
        <div class="card-footer text-center">
            <button class="btn btn-primary export-mindmap-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ get_label('export_mindmap', 'Export Mind Map') }}">
                <i class="bx bx-export"></i>
            </button>

        </div>
    </div>
</div>
<script>
    var mindMapData = @json($mindMapData);
    var projectId = @json($project -> id);
    var exportRoute = @json(route('projects.export_mindmap', ['id' => $project -> id]));
</script>
<script src="{{asset('assets/js/html2canvas.min.js')}}"></script>
<script src="{{ asset('assets/js/pages/project-mind-map.js') }}"></script>
@endsection