@extends('layouts.admin')

@section('title', 'Deep Link Analytics')

@section('content')
<div class="container-fluid pt-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="mb-0">إحصائيات روابط التحميل (Deep Links)</h2>
            <form action="{{ route('admin.reports.analytics') }}" method="GET" class="d-flex">
                <select name="link_type" class="form-control mr-2">
                    <option value="all" {{ $linkType == 'all' ? 'selected' : '' }}>جميع الروابط</option>
                    <option value="download" {{ $linkType == 'download' ? 'selected' : '' }}>روابط التحميل المباشر</option>
                    <option value="general" {{ $linkType == 'general' ? 'selected' : '' }}>الروابط العامة (محتوى)</option>
                </select>
                <select name="period" class="form-control mr-2">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>آخر 7 أيام</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>آخر 30 يوم</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>آخر 3 شهور</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>آخر سنة</option>
                </select>
                <button type="submit" class="btn btn-primary">تحديث</button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">إجمالي النقرات</h5>
                    <h2>{{ number_format($totalClicks) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">نقرات روابط التحميل</h5>
                    <h2>{{ number_format($downloadClicks) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">نقرات المحتوى (بوستات/أندية)</h5>
                    <h2>{{ number_format($generalClicks) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- OS Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">نقرات حسب نوع الجهاز (OS)</div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="osChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Country Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">أكثر 10 دول</div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="countryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Chart -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">النقرات عبر الزمن (آخر {{ $period }} يوم)</div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="timelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // OS Data
    const osData = @json($osData);
    const osLabels = osData.map(item => item.os_type || 'Unknown');
    const osCounts = osData.map(item => item.count);

    new Chart(document.getElementById('osChart'), {
        type: 'pie',
        data: {
            labels: osLabels,
            datasets: [{
                data: osCounts,
                backgroundColor: ['#36a2eb', '#ff6384', '#ffce56', '#4bc0c0'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Country Data
    const countryData = @json($countryData);
    const countryLabels = countryData.map(item => item.country || 'Unknown');
    const countryCounts = countryData.map(item => item.count);

    new Chart(document.getElementById('countryChart'), {
        type: 'bar',
        data: {
            labels: countryLabels,
            datasets: [{
                label: 'عدد النقرات',
                data: countryCounts,
                backgroundColor: '#36a2eb',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Timeline Data
    const dates = @json($dates);
    const clicks = @json($clicks);

    new Chart(document.getElementById('timelineChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'عدد النقرات اليومية',
                data: clicks,
                borderColor: '#36a2eb',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
