@extends('layouts.app')

@section('title', 'Admin - Edit Course')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users') }}">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin.courses') }}">
                            <i class="fas fa-graduation-cap"></i> Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.orders') }}">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Course</h1>
                <a href="{{ route('admin.courses') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Edit Course Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Course Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.update-course', $course) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="title">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $course->title) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required>{{ old('description', $course->description) }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $course->price) }}" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="teacher_id">Teacher</label>
                            <select class="form-control" id="teacher_id" name="teacher_id" required>
                                <option value="">Select a teacher</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $course->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Course
                            </button>
                            <a href="{{ route('admin.courses') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Course Information Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Course Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Course ID:</strong> {{ $course->id }}</p>
                            <p><strong>Created:</strong> {{ $course->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Last Updated:</strong> {{ $course->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Current Teacher:</strong> {{ $course->teacher->name ?? 'No Teacher Assigned' }}</p>
                            <p><strong>Current Price:</strong> ${{ number_format($course->price, 2) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-success">Active</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <h5 class="card-title">Lessons</h5>
                                    <h2>{{ $course->lessons_count ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title">Students</h5>
                                    <h2>{{ $course->students_count ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title">Revenue</h5>
                                    <h2>${{ number_format(($course->orders_count ?? 0) * $course->price, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
</script>
@endsection