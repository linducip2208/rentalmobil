<?php
namespace App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource;use Filament\Resources\Pages\CreateRecord;use Illuminate\Support\Facades\Storage;
class CreateMedia extends CreateRecord { protected static string $resource=MediaResource::class; protected function mutateFormDataBeforeCreate(array $data):array{$disk='public';$data['disk']=$disk;$data['original_name']=basename($data['path']);$data['mime_type']=Storage::disk($disk)->mimeType($data['path'])?:'application/octet-stream';$data['file_size']=Storage::disk($disk)->size($data['path']);$data['uploaded_by']=auth()->id();return$data;} }
