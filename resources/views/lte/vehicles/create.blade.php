@extends('lte.layout')

@section('title', 'Tambah Kendaraan')

@section('content')
@include('lte.vehicles.form', ['vehicle' => new App\Models\Vehicle])
@endsection
